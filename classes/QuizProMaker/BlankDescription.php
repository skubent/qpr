<?php

namespace QuizProMaker;

class BlankDescription {
    public const FILE_PICTURE = Paths::TEMPLATES_DIR . '/blank_picture.png';

    public const FONT = Paths::TEMPLATES_DIR . '/font900.ttf';

    /** координаты начала надписи про тему */
    public const THEME_START_X = 190;
    public const THEME_START_Y = 220;

    /** размеры места под вопрос */
    public const PICTURE_QUESTION_AREA_WIDTH  = 1520;
    public const PICTURE_QUESTION_AREA_HEIGHT = 620;

    /** @var int верхняя точка зоны ответа */
    public const ANSWER_START_Y = 980;

    /** @var int верхняя точка зоны вопроса */
    public const QUESTION_TOP_Y = 250;

    /** @var int нижняя точка зоны вопроса */
    public const QUESTION_BOTTOM_Y = 880;

    /** @var int ширина, в которую хотим уложиться */
    public const EFFECTIVE_WIDTH = 1600;

    /** @var int размер шрифта, которым пишем тему */
    public const THEME_FONT_SIZE = 64;

    /** @var int размер шрифта, под который сделано */
    public const QUESTION_FONT_SIZE       = 52;
    public const QUESTION_FONT_SIZE_SMALL = 36;

    /** @var int Ширина изображения */
    public const WIDTH = 2000;
}
