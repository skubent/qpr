<?php

namespace QuizProMaker;

class BlankDescription {
    public const FILE = Paths::TEMPLATES_DIR . '/blank.png';

    public const FILE_PICTURE = Paths::TEMPLATES_DIR . '/blank_picture.png';

    public const FONT = Paths::TEMPLATES_DIR . '/font900.ttf';

    /** координаты начала надписи про тему */
    public const THEME_START_X = 100;
    public const THEME_START_Y = 100;

    /** координаты начала напдиси вопроса для вопроса-картинки */
    public const PICTURE_QUESTION_X = 100;
    public const PICTURE_QUESTION_Y = 75;

    public const PICTURE_QUESTION_AREA_WIDTH  = 560;
    public const PICTURE_QUESTION_AREA_HEIGHT = 260;

    // Оно на самом деле будет отцентровано
    public const PICTURE_QUESTION_MAIN_AREA_X = 100;
    public const PICTURE_QUESTION_MAIN_AREA_Y = 98;

    /** @var int верхняя точка зоны ответа */
    public const ANSWER_START_Y = 390;

    /** @var int верхняя точка зоны ответа с картинкой */
    public const PICTURE_ANSWER_START_Y = 400;

    /** @var int верхняя точка зоны вопроса */
    public const QUESTION_TOP_Y = 125;

    /** @var int нижняя точка зоны вопроса */
    public const QUESTION_BOTTOM_Y = 345;

    /** @var int ширина, в которую хотим уложиться */
    public const EFFECTIVE_WIDTH = 600;

    /** @var int размер шрифта, которым пишем тему */
    public const THEME_FONT_SIZE = 32;

    /** @var int размер шрифта, под который сделано */
    public const QUESTION_FONT_SIZE       = 22;
    public const QUESTION_FONT_SIZE_SMALL = 18;

    /** @var int расстояние между строками */
    public const LINE_INTERVAL       = 32;
    public const LINE_INTERVAL_SMALL = 26;

    /** @var int увеличенное расстояние между строками */
    public const EXTENDED_LINE_INTERVAL = 42;

    /** @var int Ширина изображения */
    public const WIDTH = 800;

    /** @var int Наибольшая ширина темы */
    public const THEME_TEXT_WIDTH_MAX = 550;
}
