<?php
/**
 * External.
 *
 * @package mod_poodlltime
 * @author  Justin Hunt - poodll.com
 */


use mod_poodlltime\utils;
use mod_poodlltime\constants;
use mod_poodlltime\diff;

/**
 * External class.
 *
 * @package mod_poodlltime
 * @author  Justin Hunt - poodll.com
 */
class mod_poodlltime_external extends external_api {

    public static function check_by_phonetic_parameters(){
        return new external_function_parameters(
                 array('spoken' => new external_value(PARAM_TEXT, 'The spoken phrase'),
                       'correct' => new external_value(PARAM_TEXT, 'The correct phrase'),
                       'language' => new external_value(PARAM_TEXT, 'The language eg en-US')
                 )
        );

    }
    public static function check_by_phonetic($spoken, $correct, $language){
        $language = substr($language,0,2);
        $spokenphonetic = utils::convert_to_phonetic($spoken,$language);
        $correctphonetic = utils::convert_to_phonetic($correct,$language);
        $similar_percent = 0;
        $similar_chars = similar_text($correctphonetic,$spokenphonetic,$similar_percent);
        return round($similar_percent,0);

    }

    public static function check_by_phonetic_returns(){
        return new external_value(PARAM_INT,'how close is spoken to correct, 0 - 100');
    }


    public static function report_step_grade_parameters() {
        return new external_function_parameters([
                'cmid' => new external_value(PARAM_INT),
                'grade' => new external_value(PARAM_INT),
                'step' => new external_value(PARAM_INT)
        ]);
    }

    public static function report_step_grade($cmid,$grade,$step){
       // $ret= utils::update_step_grade($modid, $correct);
        return true;
    }
    public static function report_step_grade_returns() {
        return new external_value(PARAM_BOOL);
    }

    public static function compare_passage_to_transcript_parameters(){
        return new external_function_parameters(
                array('transcript' => new external_value(PARAM_TEXT, 'The spoken phrase'),
                        'passage' => new external_value(PARAM_TEXT, 'The correct phrase'),
                        'language' => new external_value(PARAM_TEXT, 'The language eg en-US'),
                        'alternatives' => new external_value(PARAM_TEXT, 'list of alternatives',false,'')
                )
        );

    }

    public static function compare_passage_to_transcript($language,$passage,$transcript, $alternatives) {
        global $DB;

        //turn the passage and transcript into an array of words
        $passagebits = diff::fetchWordArray($passage);
        $alternatives = diff::fetchAlternativesArray($alternatives);
        $transcriptbits = diff::fetchWordArray($transcript);
        $wildcards = diff::fetchWildcardsArray($alternatives);

        //fetch sequences of transcript/passage matched words
        // then prepare an array of "differences"
        $passagecount = count($passagebits);
        $transcriptcount = count($transcriptbits);
        $sequences = diff::fetchSequences($passagebits, $transcriptbits, $alternatives, $language);
        //fetch diffs
        $debug=false;
        $diffs = diff::fetchDiffs($sequences, $passagecount, $transcriptcount, $debug);
        $diffs = diff::applyWildcards($diffs, $passagebits, $wildcards);


        //from the array of differences build error data, match data, markers, scores and metrics
        $errors = new \stdClass();
        $currentword = 0;

        //loop through diffs
        $results=[];
        foreach ($diffs as $diff) {
            $currentword++;
            $result = new \stdClass();
            $result->word = $passagebits[$currentword - 1];
            $result->wordnumber = $currentword;
            switch ($diff[0]) {
                case Diff::UNMATCHED:
                    //we collect error info so we can count and display them on passage

                    $result->matched =false;
                    break;

                case Diff::MATCHED:
                    $result->matched =true;
                    break;

                default:
                    //do nothing
                    //should never get here
            }
            $results[]=$result;
        }

        //finalise and serialise session errors
        $sessionresults = json_encode($results);

        return $sessionresults;

    }
    public static function compare_passage_to_transcript_returns() {
        return new external_value(PARAM_RAW);
    }



}
