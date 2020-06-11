<?php

namespace QuizProMaker;

class LocalQuestion {
    /** @var int */
    public $id;

    /** @var string */
    public $question;

    /** @var int - стоимость вопроса */
    public $points;

    /** @var string - расшифровка ответа если нужна */
    public $extendedAnswer;

    /** @var string[] */
    private $answers = [];

    /** @var string */
    public $type;

    /** @var string */
    public $questionPicture;

    /** @var string */
    public $answerPicture;

    public function __construct() {
        $this->type = 'text';
    }

    public function getAnswers(): array {
        return $this->answers;
    }

    public function addAnswer(string $answer): void {
        $this->answers[] = $answer;
    }

    public function getMainAnswer(): string {
        return reset($this->answers) ?? '';
    }
}
