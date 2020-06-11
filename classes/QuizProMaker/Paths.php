<?php

namespace QuizProMaker;

class Paths {
    public const WORK_DIR      = __DIR__ . '/../..';
    public const TEMPLATES_DIR = __DIR__ . '/../../templates';
    public const GAMES_DIR     = __DIR__ . '/../../games';

    public static function getQuestionFilename(string $gameId): string {
        return self::getSetRoot($gameId) . '/questions.json';
    }

    public static function getSyncFilename(string $gameId): string {
        return self::getSetRoot($gameId) . '/sync.json';
    }

    public static function getQuestionPictureName(string $gameId, string $themeId, string $questionId): string {
        return self::getSetRoot($gameId) . '/pictures/question_' . $themeId . '_' . $questionId . '.png';
    }

    public static function getSetRoot(string $gameId): string {
        return self::GAMES_DIR . '/' . $gameId;
    }

    public static function getAnswerPictureName(string $gameId, string $themeId, string $questionId): string {
        return self::getSetRoot($gameId) . '/pictures/answer_' . $themeId . '_' . $questionId . '.png';
    }
}
