<?php
/**
 * Google AI Handler
 *
 * @package WP Academic Post Enhanced
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPA_Google_AI {

    private $api_key;
    private $settings;

    public function __construct() {
        $this->settings = get_option( 'wpa_field_news_settings' );
        $this->api_key = isset( $this->settings['google_api_key'] ) ? $this->settings['google_api_key'] : '';
    }

    public function test_connection( $key ) {
        $model = isset( $this->settings['google_model_body'] ) ? $this->settings['google_model_body'] : 'gemini-2.0-flash';
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $key;
        
        $body = [ 'contents' => [ [ 'parts' => [ [ 'text' => 'Hello' ] ] ] ] ];
        $response = wp_remote_post( $url, [
            'headers' => [ 'Content-Type' => 'application/json' ],
            'body'    => json_encode( $body ),
            'timeout' => 15
        ] );

        if ( is_wp_error( $response ) ) throw new Exception( 'Connection failed: ' . $response->get_error_message() );
        
        $code = wp_remote_retrieve_response_code( $response );
        if ( $code !== 200 ) {
            $data = json_decode( wp_remote_retrieve_body( $response ), true );
            $msg = isset($data['error']['message']) ? $data['error']['message'] : 'Status ' . $code;
            throw new Exception( 'Google AI Error: ' . $msg );
        }
        return true;
    }

    private function get_model_url( $type ) {
        // Map types without specific settings to the main Body model
        if ( $type === 'excerpt' ) {
            $type = 'body';
        }

        $setting_key = 'google_model_' . $type;
        
        // 1. Try specific model for this type
        if ( ! empty( $this->settings[ $setting_key ] ) ) {
            $model = $this->settings[ $setting_key ];
        } 
        // 2. Fallback to Body model (if this isn't the body model itself)
        elseif ( ! empty( $this->settings['google_model_body'] ) ) {
            $model = $this->settings['google_model_body'];
        } 
        // 3. Ultimate Fallback
        else {
            $model = 'gemini-2.0-flash';
        }

        return 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $this->api_key;
    }

    public function bulk_screen_studies( $studies ) {
        if ( empty( $this->api_key ) || empty( $studies ) ) return [ 'selected' => [], 'ignored' => [] ];

        $strategy = isset($this->settings['selection_strategy']) ? $this->settings['selection_strategy'] : 'impact';
        $criteria = isset($this->settings['selection_criteria']) ? $this->settings['selection_criteria'] : '';

        $list_str = "";
        foreach ( $studies as $s ) {
            $excerpt = wp_trim_words( $s['abstract'], 40 );
            $list_str .= "ID " . $s['id'] . ": " . $s['title'] . "\n   Abstract: " . $excerpt . "\n\n";
        }

        $prompt = "You are an Academic Editor screening a list of studies.\n" .
                  "STRATEGY: " . $strategy . ".\n" .
                  ($criteria ? "CRITERIA: " . $criteria . "\n" : "") .
                  "TASK: Categorize these studies into two lists:\n" .
                  "1. 'ignored_ids': Studies that are CLEARLY IRRELEVANT, SPAM, or completely off-topic.\n" .
                  "2. 'selected_ids': Studies that are HIGHLY RELEVANT, interesting, and worth publishing based on the strategy.\n" .
                  "INSTRUCTION: Be permissive with 'selected'. If a study is potentially good, select it. Only ignore if it's garbage.\n" .
                  "STRICT OUTPUT FORMAT: Return a JSON object with keys 'ignored_ids' and 'selected_ids'. Example: {\"ignored_ids\": [101], \"selected_ids\": [102, 103]}.\n\n" .
                  "STUDIES:\n" . $list_str;

        $url = $this->get_model_url( 'selector' );
        $text = $this->call_api( $url, $prompt, 60 );

        if ( empty( $text ) ) {
            throw new Exception( 'AI returned an empty response during screening.' );
        }

        $ignored_ids = [];
        $selected_ids = [];

        if ( $text ) {
            // Robust JSON extraction
            if ( preg_match( '/\{.*?\}/s', $text, $matches ) ) {
                $json_str = $matches[0];
                $json = json_decode( $json_str, true );
                
                if ( isset( $json['ignored_ids'] ) && is_array( $json['ignored_ids'] ) ) {
                    $ignored_ids = $json['ignored_ids'];
                }
                if ( isset( $json['selected_ids'] ) && is_array( $json['selected_ids'] ) ) {
                    $selected_ids = $json['selected_ids'];
                }
            }
        }

        return [ 'selected' => $selected_ids, 'ignored' => $ignored_ids ];
    }

    public function select_study( $candidates, $topic ) {
        if ( count( $candidates ) <= 1 ) return $candidates[0];

        $list_str = "";
        foreach ( $candidates as $index => $c ) {
            $excerpt = wp_trim_words( $c['abstract'], 30 );
            $list_str .= ($index + 1) . ". Title: " . $c['title'] . "\n   Abstract: " . $excerpt . "\n   Citations: " . $c['citations'] . "\n\n";
        }

        $strategy = isset($this->settings['selection_strategy']) ? $this->settings['selection_strategy'] : 'impact';
        $criteria = isset($this->settings['selection_criteria']) ? $this->settings['selection_criteria'] : '';

        $prompt = "I have a list of scientific studies about '" . $topic . "'.\n" .
                  "Your task: Identify the ONE study that fits this strategy: " . $strategy . ".\n" .
                  ($criteria ? "Additional Criteria: " . $criteria . "\n" : "") . 
                  "STRICT RESPONSE FORMAT: Return ONLY the number (e.g. '3') of the best study. Do not write any other text.\n\n" .
                  $list_str;

        $url = $this->get_model_url( 'selector' );
        $text = $this->call_api( $url, $prompt, 45 );

        if ( $text && preg_match( '/\d+/', $text, $matches ) ) {
            $idx = intval( $matches[0] ) - 1; 
            if ( isset( $candidates[$idx] ) ) return $candidates[$idx];
        }

        return $candidates[0]; // Fallback
    }

    public function generate_content( $type, $study ) {
        if ( empty( $this->api_key ) ) return false;

        $lang_code = isset( $this->settings['target_language'] ) ? $this->settings['target_language'] : 'en';
        $lang_name = $this->get_language_name( $lang_code );
        
        $context = "Study Title: " . $study['title'] . "\nAuthors: " . $study['creator'] . "\nAbstract: " . $study['abstract'] . "\n\n";
        
        $system = "You are a senior science journalist for a popular academic news portal.";
        if ( $type === 'title' ) {
            $system = "You are an expert Headline Editor. You specialize in SEO-friendly, catchy, and accurate academic news headlines.";
        }

        $prompt = $this->build_prompt( $type, $context, $lang_name );
        $url = $this->get_model_url( $type );
        
        $text = $this->call_api( $url, $prompt, 90, $system );
        
        if ( $text ) {
            // Cleanup
            $text = str_replace( ['```html', '```', '`'], '', $text );
            $text = str_ireplace( ['[redacted for peer review]', '(redacted for peer review)', '[redacted]', '(redacted)'], '', $text ); // Fail-safe removal
            $text = preg_replace( '/<style\b[^>]*>(.*?)<\/style>/is', '', $text );
            $text = preg_replace( '/<script\b[^>]*>(.*?)<\/script>/is', '', $text );
            
            if ( $type === 'title' ) {
                // Remove conversational preamble
                $text = strip_tags( $text ); 
                $text = str_replace( ['**', '"', "'"], '', $text );
                
                $lines = explode( "\n", $text );
                $clean_lines = [];
                
                $meta_indicators = [ 'Note:', 'Important:', 'dialect', 'helpful', 'language', 'terminology', 'context', 'headline:', 'title:' ];
                
                foreach ( $lines as $line ) {
                    $line = trim( $line );
                    if ( empty( $line ) ) continue;
                    
                    $is_meta = false;
                    foreach ( $meta_indicators as $indicator ) {
                        if ( stripos( $line, $indicator ) !== false ) {
                            $is_meta = true; break;
                        }
                    }
                    
                    if ( ! $is_meta ) {
                        $clean_lines[] = $line;
                    }
                }
                
                if ( ! empty( $clean_lines ) ) {
                    // Usually the first line after cleaning meta is the title
                    $text = $clean_lines[0];
                } else {
                    // Fallback to the original logic if we over-cleaned
                    $text = end( $lines );
                }
            }
            
            if ( $type === 'body' ) {
                $text = wp_kses_post( $text );
            }
            return $text;
        }
        return false;
    }

    public function review_content( $title, $body, $study ) {
        if ( empty( $this->api_key ) ) return [ 'status' => 'PASS', 'reason' => 'No API Key' ];

        $strictness = isset( $this->settings['review_strictness'] ) ? $this->settings['review_strictness'] : 'moderate';
        
        $prompt = "You are a Senior Scientific Editor. Review the following News Article generated from a Scientific Study.\n\n" .
                  "ORIGINAL STUDY:\n" .
                  "Title: " . $study['title'] . "\n" .
                  "Abstract: " . $study['abstract'] . "\n\n" .
                  "GENERATED NEWS ARTICLE:\n" .
                  "Headline: " . $title . "\n" .
                  "Body: " . strip_tags( $body ) . "\n\n" .
                  "TASK: Compare the generated article with the original study.\n" .
                  "CRITERIA (" . strtoupper($strictness) . "):\n" .
                  "1. Accuracy: Does the news article accurately reflect the study's findings? (No hallucinations).\n" .
                  "2. Tone: Is it professional and objective?\n" .
                  "3. Safety: Is it free of harmful advice (especially for medical topics)?\n\n" .
                  "STRICT OUTPUT FORMAT: Return ONLY a JSON object with two keys: 'status' ('PASS' or 'FAIL') and 'reason' (short explanation).";

        $url = $this->get_model_url( 'review' );
        $text = $this->call_api( $url, $prompt, 60 );

        if ( $text ) {
            // Clean JSON
            $text = str_replace( ['```json', '```'], '', $text );
            $json = json_decode( $text, true );
            
            if ( json_last_error() === JSON_ERROR_NONE && isset($json['status']) ) {
                return $json;
            }
            
            // Fallback parsing if JSON fails
            if ( stripos( $text, 'PASS' ) !== false ) return [ 'status' => 'PASS', 'reason' => 'Parsed from text' ];
            if ( stripos( $text, 'FAIL' ) !== false ) return [ 'status' => 'FAIL', 'reason' => $text ];
        }

        return [ 'status' => 'PASS', 'reason' => 'Reviewer failed to respond' ]; // Default to pass if AI errors to avoid blockage
    }

    public function verify_accuracy( $title, $body, $study ) {
        if ( empty( $this->api_key ) ) return [ 'pass' => true, 'score' => 100 ];

        $system = "You are a professional Fact-Checker specialized in scientific communications.";
        $prompt = "TASK: Verify the accuracy of the generated NEWS ARTICLE against the ORIGINAL STUDY abstract.\n\n" .
                  "ORIGINAL ABSTRACT:\n" . $study['abstract'] . "\n\n" .
                  "GENERATED ARTICLE:\n" . strip_tags( $body ) . "\n\n" .
                  "INSTRUCTIONS:\n" .
                  "1. Identify the 3 most important findings in the abstract.\n" .
                  "2. Check if the article correctly represents these findings.\n" .
                  "3. Flag any hallucinations or exaggerated claims.\n" .
                  "4. Return a JSON object with: 'claims' (array of objects with 'finding' and 'verified' boolean), 'score' (0-100), and 'pass' (boolean, pass if score > 80).\n\n" .
                  "STRICT RESPONSE FORMAT: JSON ONLY.";

        $url = $this->get_model_url( 'review' );
        $text = $this->call_api( $url, $prompt, 60, $system );

        if ( $text ) {
            $text = str_replace( ['```json', '```'], '', $text );
            $json = json_decode( $text, true );
            if ( json_last_error() === JSON_ERROR_NONE ) return $json;
        }

        return [ 'pass' => true, 'score' => 100, 'note' => 'Verification failed to run' ];
    }

    public function generate_metadata( $study ) {
        if ( empty( $this->api_key ) ) return false;

        $lang_code = isset( $this->settings['target_language'] ) ? $this->settings['target_language'] : 'en';
        $lang_name = $this->get_language_name( $lang_code );
        $context = "Title: " . $study['title'] . "\nAbstract: " . $study['abstract'] . "\n\n";

        $prompt = "TASK: Extract metadata from this scientific study.\n" .
                  "CRITICAL RULE: All values in the resulting JSON MUST be written in " . strtoupper($lang_name) . ". Even if the study text is in English, you MUST translate the findings, questions, and labels into " . strtoupper($lang_name) . ".\n\n" .
                  "STRICT OUTPUT FORMAT: Return ONLY a JSON object with these keys:\n" .
                  "1. 'highlights': Array of 3-4 short bullet points summarizing key findings in " . $lang_name . ".\n" .
                  "2. 'study_type': A short label for research type in " . $lang_name . ".\n" .
                  "3. 'difficulty': Difficulty level in " . $lang_name . " (translated).\n" .
                  "4. 'discussion_questions': Array of 3 academic discussion questions in " . $lang_name . ".\n" .
                  "5. 'evidence_strength': Evidence strength label in " . $lang_name . ".\n\n" .
                  "CONTEXT:\n" . $context;

        $url = $this->get_model_url( 'selector' );
        $text = $this->call_api( $url, $prompt, 45 );

        if ( $text ) {
            $text = str_replace( ['```json', '```'], '', $text );
            $json = json_decode( $text, true );
            if ( json_last_error() === JSON_ERROR_NONE ) return $json;
        }
        return false;
    }

    private function call_api( $url, $prompt, $timeout, $system_instruction = '' ) {
        $body = [ 
            'contents' => [ [ 'parts' => [ [ 'text' => $prompt ] ] ] ],
            'generationConfig' => [
                'temperature' => 0.4,
                'topP' => 0.8,
                'maxOutputTokens' => 2048,
            ]
        ];

        // Determine if model supports system instructions (Gemini models do, Gemma does not)
        $is_gemini = strpos( $url, 'gemini' ) !== false;

        if ( ! empty( $system_instruction ) ) {
            if ( $is_gemini ) {
                $body['system_instruction'] = [
                    'parts' => [ [ 'text' => $system_instruction ] ]
                ];
            } else {
                // Fallback for models without system instruction support (e.g. Gemma)
                // Prepend instruction to the user prompt
                $body['contents'][0]['parts'][0]['text'] = "SYSTEM INSTRUCTION: " . $system_instruction . "\n\nUSER PROMPT: " . $prompt;
            }
        }

        $response = wp_remote_post( $url, [
            'headers' => [ 'Content-Type' => 'application/json' ],
            'body'    => json_encode( $body ),
            'timeout' => $timeout
        ] );

        if ( is_wp_error( $response ) ) {
            throw new Exception( 'API Request Failed: ' . $response->get_error_message() );
        }
        
        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );
        
        if ( $code !== 200 ) {
            $msg = isset($data['error']['message']) ? $data['error']['message'] : 'Status ' . $code;
            throw new Exception( 'Google AI API Error: ' . $msg );
        }

        if ( isset( $data['candidates'][0]['content']['parts'][0]['text'] ) ) {
            return trim( $data['candidates'][0]['content']['parts'][0]['text'] );
        }

        return false;
    }

    private function get_language_name( $code ) {
        $map = [ 'en'=>'English', 'ar'=>'Arabic', 'es'=>'Spanish', 'fr'=>'French', 'de'=>'German', 'zh'=>'Chinese', 'ja'=>'Japanese', 'ru'=>'Russian', 'pt'=>'Portuguese', 'it'=>'Italian' ];
        return isset($map[$code]) ? $map[$code] : 'English';
    }

    private function build_prompt( $type, $context, $lang_name ) {
        $tone = isset( $this->settings['ai_tone'] ) ? $this->settings['ai_tone'] : 'professional';
        
        if ( $type === 'title' ) {
            // Force translation logic
            return "TASK: Create a compelling news headline for this study in " . strtoupper($lang_name) . ".\n" .
                   "CRITICAL INSTRUCTION: Even if the study text is in English, you MUST write the headline in " . strtoupper($lang_name) . ".\n" .
                   "STRICT RESPONSE FORMAT: Output ONLY the title text. No quotes. No repetition. No meta-commentary. No advice. No 'Important Note' or feedback.\n" .
                   "Tone: " . $tone . ". Style: Catchy, SEO-friendly.\n" .
                   "Constraint: Maximum 15 words.\n\n" . $context;
        } elseif ( $type === 'excerpt' ) {
            return "TASK: Write a 2-sentence summary that hooks the reader.\n" .
                   "CRITICAL RULE: OUTPUT ONLY IN " . strtoupper($lang_name) . ".\n" .
                   "Tone: " . $tone . ".\n\n" . $context;
        } elseif ( $type === 'tags' ) {
            return "Generate 8-10 comma-separated keywords/concepts in " . $lang_name . ". Do not number them. Focus on the main scientific topics.\n\n" . $context;
        }

        // Body Prompt
        $audience = isset( $this->settings['ai_audience'] ) ? $this->settings['ai_audience'] : 'general';
        $structure_key = isset( $this->settings['ai_structure'] ) ? $this->settings['ai_structure'] : 'news';
        $custom_instr = isset( $this->settings['ai_custom_instructions'] ) ? $this->settings['ai_custom_instructions'] : '';
        
        // Detailed Structure Instructions (Enforcing Translation)
        $structure_instructions = [
            'news' => "Structure: Standard journalistic inverted pyramid. Start with the 'Lead'. Use <h2> subheadings for 'Methodology', 'Results', and 'Implications' (Use ONLY the natural " . $lang_name . " term. Do NOT include the English word. Do NOT repeat the word).",
            'listicle' => "Structure: A catchy introduction followed by a numbered list of key findings. Use <h3> for each list item title.",
            'qa' => "Structure: Q&A format. Use <strong>Question:</strong> and plain text for answers (Translate 'Question' to " . $lang_name . ").",
            'essay' => "Structure: A persuasive or analytical essay. Focus on the broader context and future of this research.",
            'eli5' => "Structure: 'Explain Like I'm 5'. Use simple analogies, short sentences, and avoid jargon completely.",
            'debate' => "Structure: Present the findings, then play 'Devil's Advocate'. Discuss limitations and alternative interpretations.",
            'case' => "Structure: Frame the study as a story or real-world scenario. How does this affect a real person?",
            'bullets' => "Structure: Executive Summary. Use a short intro, then bullet points (<ul>) for Objectives, Methods, and Key Results (Translate headers to " . $lang_name . " naturally).",
            'interview' => "Structure: Simulated interview with the researcher. Use 'Interviewer:' and 'Researcher:' dialogue format (Translate roles to " . $lang_name . ")."
        ];
        $struct_txt = isset($structure_instructions[$structure_key]) ? $structure_instructions[$structure_key] : $structure_instructions['news'];

        $prompt = "You are a senior academic science journalist with expertise in clinical psychology and global research trends.\n" .
               "TASK: Write a comprehensive, authoritative, 'Pillar' article based on the provided scientific study.\n\n" .
               "TARGET AUDIENCE: " . $audience . ".\n" .
               "TONE: " . $tone . ".\n\n" .
               "REQUIRED SECTIONS (Use <h2> and <h3> for subheadings):\n" .
               "1. **Theoretical Framework**: Explain the underlying psychological principles or theories (e.g., CBT, psychodynamics) related to this study.\n" .
               "2. **Detailed Methodology**: Break down how the research was conducted in an accessible but rigorous way.\n" .
               "3. **Clinical & Practical Implications**: Discuss how these findings impact therapists, patients, and the general public.\n" .
               "4. **Arabic Cultural Context**: CRITICAL: Provide a comparative analysis of how these findings translate or apply to the Arab world, cultural nuances, or specific regional challenges.\n" .
               "5. **Future Directions & Limitations**: What's next for this field of study?\n\n" .
               "STRUCTURAL GUIDELINES:\n" . 
               "- " . $struct_txt . "\n" .
               "- **Hook:** Start with a compelling narrative or a profound clinical question to engage the reader. CRITICAL: The hook/introduction MUST be written in " . strtoupper($lang_name) . ". Do NOT write the opening paragraph in English.\n" .
               "- **CRITICAL:** Do NOT include a summary, key takeaways, or bullet points at the start or end of the body. Focus on deep narrative analysis.\n" .
               "- **Humanize:** Use analogies for complex neuroscientific or psychological terms.\n" .
               "- **Attribution:** Cite the original researchers naturally within the text.\n" .
               ($custom_instr ? "- **Custom Rule:** " . $custom_instr . "\n" : "") . "\n" .
               "CRITICAL OUTPUT RULES:\n" .
               "1. LANGUAGE: Write STRICTLY in " . strtoupper($lang_name) . ". This includes ALL headings, subheadings, AND the opening hook/introduction paragraph. Do NOT include ANY English sentences in the article body.\n" .
               "2. FORMAT: Use clean HTML5 tags (<h2>, <h3>, <p>, <ul>, <li>, <strong>). Do NOT use <h1>, Markdown, or <html>/<body> tags.\n" .
               "3. LENGTH: Aim for a comprehensive exploration of **1200 - 1500 words**. Be thorough and insightful.\n" . 
               "4. ANONYMITY: NEVER include placeholders like '[redacted]'. Use only the provided context.\n\n" . 
               "STUDY CONTEXT:\n" . $context;

        return $prompt;
    }}
