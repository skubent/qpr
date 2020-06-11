<?php

namespace QuizProMaker;

class GameDescription {

    private $game;

    /** @var LocalTheme[] */
    public $themes;

    public static function loadFromFile(string $fileName): self {
        $json       = json_decode(file_get_contents($fileName));
        $rv         = new self($json->name, $json->description);
        $rv->themes = [];
        foreach ($json->themes as $themeId => $themeObj) {
            $theme = new LocalTheme();
            $theme->id   = $themeId;
            $theme->name = $themeObj->name;
            $questions = [];
            foreach ($themeObj->questions as $questionId => $questionObj) {
                $question = new LocalQuestion();
                if (isset($questionObj->type)) {
                    $question->type = $questionObj->type;
                }
                if (isset($questionObj->picture_question)) {
                    $question->questionPicture = $questionObj->picture_question;
                }
                if (isset($questionObj->picture_answer)) {
                    $question->answerPicture = $questionObj->picture_answer;
                }
                $question->id             = $questionId;
                $question->question       = $questionObj->text;
                foreach ($questionObj->answers as $answer) {
                    $question->addAnswer($answer);
                }
                $question->points         = $questionObj->points ?? 1;
                $question->extendedAnswer = $questionObj->extended ?? '';
                $questions[] = $question;

                if ($question->type == 'picture' && !$question->questionPicture) {
                    echo "Question {$question->question} have type picture and have not picture_question field\n";
                    exit;
                }

                $rv->addQuestion($json->timeToAnswer, $theme->name, $question);
            }
            $theme->questions = $questions;
            $rv->themes[] = $theme;
        }
        return $rv;
    }
    public function __construct(string $gameName, string $gameDescription) {
        $this->game = [
            'description' => $gameDescription,
            'rounds'      => [],
            'streams_id'  => [],
            'title'       => $gameName,
        ];
    }

    public function addQuestion(int $timeout, string $themeName, LocalQuestion $question) {
        $ans = [];
        foreach ($question->getAnswers() as $answer) {
            $ans[] = [
                'answer'     => $answer,
                'is_correct' => false,
                'sort'       => count($ans) + 1,
            ];
        }
        $this->game['rounds'][] = [
            'answer'         => $question->extendedAnswer,
            'answer_media'   => null,
            'answers'        => $ans,
            'is_diff_points' => false,
            'points'         => $question->points,
            'question'       => "Наш сайт и инстаграм - askr.pro",
            'question_media' => null,
            'round_type'     => 'input',
            'sort'           => count($this->game['rounds']) + 1,
            'timeout'        => $timeout,
        ];
    }

    public function getAsJson(): string {
        return json_encode($this->game, JSON_UNESCAPED_UNICODE);
    }

    public function getAsQuestionJson(): string {
        $themes = [];
        foreach ($this->themes as $theme) {
            $questions = [];
            foreach ($theme->questions as $question) {
                $q = [];
                if ($question->type != 'text') {
                    $q['type'] = $question->type;
                }
                if ($question->questionPicture) {
                    $q['picture_question'] = $question->questionPicture;
                }
                if ($question->answerPicture) {
                    $q['picture_answer'] = $question->answerPicture;
                }
                $q['text'] = $question->question;
                $an = [];
                foreach ($question->getAnswers() as $answer) {
                    $an[] = $answer;
                }
                $q['answers'] = $an;
                $questions[] = $q;
            }
            $themes[] = [
                'name' => $theme->name,
                'questions' => $questions
            ];
        }
        $rv = [
            'name'         => 'Auto prepared game',
            'description'  => 'Auto prepared description',
            'timeToAnswer' => 30,
            'themes'       => $themes,
        ];
        return json_encode($rv, JSON_UNESCAPED_UNICODE);
    }
}
