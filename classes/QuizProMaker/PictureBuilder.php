<?php

namespace QuizProMaker;

class PictureBuilder {
    /** @var string */
    private $theme;

    /** @var string */
    private $question;

    /** @var string */
    private $answer;

    private $image;
    private $mainColor;

    /** @var string */
    private $pictureAnswerFileName;
    /** @var string */
    private $pictureQuestionFileName;

    /** @var string */
    private $mode;

    /** @var string  */
    public $setRoot;

    public function __construct(LocalTheme $localTheme, LocalQuestion $localQuestion) {
        $this->theme     = $localTheme->name;
        $this->question  = $localQuestion->question;
        $this->answer    = $localQuestion->getMainAnswer();

        if (
            $localQuestion->type != 'text'
            && $localQuestion->type != 'picture'
            && $localQuestion->type != 'picture_answer'
        ) {
            throw new \Exception("Cant create picture for question type {$localQuestion->type}");
        }

        $this->mode = $localQuestion->type;

        $this->pictureAnswerFileName   = $localQuestion->answerPicture;
        $this->pictureQuestionFileName = $localQuestion->questionPicture;
    }

    private function prepareImage(): void {
        $this->image     = imagecreatefrompng(BlankDescription::FILE_PICTURE);
        $this->mainColor = imagecolorallocate($this->image, 0xd8, 0x0a, 0x05);
    }

    public function createBothPictures(string $questionFileName, string $answerFileName): void {
        if ($this->mode == 'text') {
            $this->prepareImage();

            $this->createQuestion();
            imagepng($this->image, $questionFileName);

            $this->addAnswer();
            imagepng($this->image, $answerFileName);
        } elseif ($this->mode == 'picture') {
            $this->prepareImage();
            $this->createPictureQuestion();
            imagepng($this->image, $questionFileName);

            $this->prepareImage();
            $this->createPictureAnswer();
            imagepng($this->image, $answerFileName);
        } elseif ($this->mode == 'picture_answer') {
            $this->prepareImage();

            $this->createQuestion();
            imagepng($this->image, $questionFileName);

            $this->prepareImage();
            $this->createPictureAnswer();
            imagepng($this->image, $answerFileName);
        } else {
            echo "Cant process mode {$this->mode}\n";
        }
    }

    private function createPictureQuestion(): void {
        $this->addTextInThemeArea($this->question);
        $this->addImageIntoMainArea($this->pictureQuestionFileName);
    }

    private function createPictureAnswer(): void {
        $this->addTextInThemeArea($this->question);
        $this->addImageIntoMainArea($this->pictureAnswerFileName ? $this->pictureAnswerFileName : $this->pictureQuestionFileName);
        $this->addAnswer();
    }

    private function addImageIntoMainArea(string $baseFileName): void {
        if (!$baseFileName) {
            throw new \Exception("Empty picture question file name found");
        }

        $fileName = $this->setRoot . '/' . $baseFileName;

        if (!file_exists($fileName) || !is_readable($fileName)) {
            throw new \Exception("Cant load file {$fileName}");
        }

        $imageToAdd = imagecreatefromstring(file_get_contents($fileName));

        $width  = imagesx($imageToAdd);
        $height = imagesy($imageToAdd);
        $scaleX = 1.0;
        $scaleY = 1.0;
        if ($width > BlankDescription::PICTURE_QUESTION_AREA_WIDTH) {
            $scaleX = $width / BlankDescription::PICTURE_QUESTION_AREA_WIDTH;
        }
        if ($height > BlankDescription::PICTURE_QUESTION_AREA_HEIGHT) {
            $scaleY = $height / BlankDescription::PICTURE_QUESTION_AREA_HEIGHT;
        }

        $scale = max($scaleX, $scaleY);

        if ($scale == 1.0) {
            // Типа считаем что мы можем увеличить это безобразие
            if ($width < BlankDescription::PICTURE_QUESTION_AREA_WIDTH) {
                $scaleX = BlankDescription::PICTURE_QUESTION_AREA_WIDTH / $width;
            }

            if ($height < BlankDescription::PICTURE_QUESTION_AREA_HEIGHT) {
                $scaleY = BlankDescription::PICTURE_QUESTION_AREA_HEIGHT / $height;
            }

            if ($scaleX > 1.4 && $scaleY > 1.4) {
                $scale = 1 / min($scaleX, $scaleY);
            }
        }

        $newWidth  = $width / $scale;
        $newHeight = $height / $scale;

        if (
            $newWidth > BlankDescription::PICTURE_QUESTION_AREA_WIDTH
            || $newHeight > BlankDescription::PICTURE_QUESTION_AREA_HEIGHT
        ) {
            echo $baseFileName . ' image resize strange size' . PHP_EOL;
            echo $width . 'x' . $height . PHP_EOL;
            echo $scale . PHP_EOL;
            echo 'new width: ' . $newWidth . PHP_EOL;
            echo 'new height: ' . $newHeight . PHP_EOL;
        }

        $imagePositionY =
            BlankDescription::QUESTION_TOP_Y +
            (
                (
                    BlankDescription::QUESTION_BOTTOM_Y
                    - BlankDescription::QUESTION_TOP_Y
                    - $newHeight
                ) / 2
            );

        imagecopyresized(
            $this->image,
            $imageToAdd,
            (BlankDescription::WIDTH - $newWidth) / 2,
            $imagePositionY,
            0,
            0,
            $newWidth,
            $newHeight,
            $width,
            $height
        );
    }

    private function createQuestion() {
        $fontSize = BlankDescription::QUESTION_FONT_SIZE;
        if (mb_strlen($this->question) > 180) {
            $fontSize = BlankDescription::QUESTION_FONT_SIZE_SMALL;
        }
        $interval = $fontSize * 1.5;
        $questionLines    = $this->explodeString($this->question, $fontSize);
        $splittedQuestion = implode("\n", $questionLines);

        $textSize    = imagettfbbox(
            $fontSize,
            0,
            BlankDescription::FONT,
            $splittedQuestion
        );

        $imageHeight = $textSize[3] - $textSize[5];

        $textPositionY =
            BlankDescription::QUESTION_TOP_Y +
            (
                (
                    BlankDescription::QUESTION_BOTTOM_Y
                    - BlankDescription::QUESTION_TOP_Y
                    - $imageHeight
                ) / 2
            );

        $currentY = $textPositionY;

        foreach ($questionLines as $lineNum => $line) {
            $currentY += $interval;
            $this->addCenteredText($currentY, $line, $fontSize);
        }

        $this->addTextInThemeArea($this->theme);
    }

    private function addTextInThemeArea(string $text): void {
        $textSize = imagettfbbox(
            BlankDescription::THEME_FONT_SIZE,
            0,
            BlankDescription::FONT,
            $text
        );

        $imageWidth = $textSize[2] - $textSize[0];
        $fontScale = 1;
        if ($imageWidth > 1600) {
            $fontScale = 1600 / $imageWidth;
        }
        $this->addText(
            BlankDescription::THEME_START_X,
            BlankDescription::THEME_START_Y,
            $text,
            BlankDescription::THEME_FONT_SIZE * $fontScale
        );
    }

    private function addAnswer() {
        $this->addCenteredText(BlankDescription::ANSWER_START_Y, $this->answer);
    }

    private function addCenteredText(int $y, string $text, ?int $fontSize = BlankDescription::QUESTION_FONT_SIZE): void {
        $textPositionX = (BlankDescription::WIDTH - $this->getStringWidth($text, $fontSize)) / 2;
        $this->addText($textPositionX, $y, $text, $fontSize);
    }

    private function addText(int $x, int $y, string $text, ?int $fontSize = BlankDescription::QUESTION_FONT_SIZE): void {
        imagettftext(
            $this->image,
            $fontSize,
            0,
            $x,
            $y,
            $this->mainColor,
            BlankDescription::FONT,
            $text
        );
    }

    private function getStringWidth(string $str, int $fontSize): int {
        $textSize = imagettfbbox($fontSize, 0, BlankDescription::FONT, $str);
        return $textSize[2] - $textSize[0];
    }

    private function explodeString(string $source, int $fontSize): array {
        $retVal = [];
        if (strpos($source, "\n") !== false) {
            $stringParts = explode("\n", $source);
            foreach ($stringParts as $oneString) {
                $retVal = array_merge($retVal, $this->explodeString($oneString, $fontSize));
            }
            return $retVal;
        }
        $words = preg_split('/\s+/', $source);
        $str   = '';
        foreach ($words as $word) {
            $tmpStr = $str . ' ' . $word;
            if ($this->getStringWidth($tmpStr, $fontSize) >= BlankDescription::EFFECTIVE_WIDTH) {
                $retVal[] = $str;
                $str      = $word;
            } else {
                $str = $tmpStr;
            }
        }
        if ($str != '') {
            $retVal[] = $str;
        }
        return $retVal;
    }
}
