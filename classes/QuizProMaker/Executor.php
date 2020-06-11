<?php

namespace QuizProMaker;

use stdClass;

class Executor {

    public static function doPicturesUpload(string $gameId): void {
        $questionsFile = Paths::getQuestionFilename($gameId);
        $syncFileName  = Paths::getSyncFilename($gameId);

        if (!file_exists($questionsFile) || !file_exists($syncFileName)) {
            echo 'File ' . $questionsFile . ' or ' . $syncFileName . ' not found' . PHP_EOL;
            return;
        }

        $game       = GameDescription::loadFromFile($questionsFile);
        $syncedGame = json_decode(file_get_contents($syncFileName));

        if (empty($syncedGame->questions)) {
            echo 'Game unsynced, run "server" mode first' . PHP_EOL;
            return;
        }

        $errors = false;
        $count  = 0;
        foreach ($game->themes as $localTheme) {
            foreach ($localTheme->questions as $localQuestion) {
                $questionFileName = Paths::getQuestionPictureName($gameId, $localTheme->id, $localQuestion->id);
                if (!file_exists($questionFileName)) {
                    echo 'Question picture ' . $questionFileName . ' not found' . PHP_EOL;
                    $errors = true;
                }
                $answerFileName = Paths::getAnswerPictureName($gameId, $localTheme->id, $localQuestion->id);
                if (!file_exists($answerFileName)) {
                    echo 'Answer picture ' . $answerFileName . ' not found' . PHP_EOL;
                    $errors = true;
                }
                if (!isset($syncedGame->questions[$count])) {
                    echo 'Not found question num ' . $count . PHP_EOL;
                    $errors = true;
                }
                $count++;
            }
        }

        if (count($syncedGame->questions) != $count) {
            echo 'Count questions (' . $count . ') and synced file (' . count($syncedGame->questions) . ') not match'  . PHP_EOL;
            $errors = true;
        }

        if ($errors) {
            echo 'Break execution' . PHP_EOL;
            return;
        }

        // закончены тесты, можно помалу грузить картинки

        $count  = 0;
        foreach ($game->themes as $localTheme) {
            foreach ($localTheme->questions as $localQuestion) {
                $questionFileName = Paths::getQuestionPictureName($gameId, $localTheme->id, $localQuestion->id);
                $answerFileName   = Paths::getAnswerPictureName($gameId, $localTheme->id, $localQuestion->id);
                $questionId       = $syncedGame->questions[$count];
                $serverGameId     = $syncedGame->gameId;

                $client = new ApiClient();
                $client->uploadQuestionFile($serverGameId, $questionId, $questionFileName);
                echo ' ' . $questionFileName . ' uploaded' . PHP_EOL;
                $client->uploadAnswerFile($serverGameId, $questionId, $answerFileName);
                echo ' ' . $answerFileName . ' uploaded' . PHP_EOL;
                $count++;
            }
        }
    }

    public static function doServerCheck(string $gameId): void {
        $syncFileName = Paths::getSyncFilename($gameId);
        if (!file_exists($syncFileName)) {
            echo 'Sync file ' . $syncFileName . ' not found' . PHP_EOL;
            return;
        }
        $syncedGame = json_decode(file_get_contents($syncFileName));
        echo 'Server game id found: ' . $syncedGame->gameId . PHP_EOL;
        $client     = new ApiClient();
        $serverGame = $client->loadGame($syncedGame->gameId);
        if (!$serverGame) {
            die('Server not return game ' . $syncedGame->gameId . ', check games on server or remove sync.json');
        }
        $unjsonedGame = json_decode($serverGame);
        if (!$unjsonedGame->result || $unjsonedGame->result != 'ok') {
            die('Server not return game ' . $syncedGame->gameId . ', check games on server or remove sync.json');
        }

        $syncedGame->questions = [];

        echo count($unjsonedGame->data->rounds) . ' rounds found' . PHP_EOL;
        foreach ($unjsonedGame->data->rounds as $round) {
            echo $round->id . ': ' .$round->question;
            echo !$round->question_media ? ' (no question picture) ' : ' question picture: ' . $round->question_media . ' ';
            echo !$round->answer_media ? '(no answer picture)' : ' answer picture: ' . $round->asnwer_media . ' ';
            echo PHP_EOL;
            $syncedGame->questions[] = $round->id;
        }
        file_put_contents($syncFileName, json_encode($syncedGame));
        /**
        ["answers"]=>
        array(1) {
        [0]=>
        object(stdClass)#6 (4)
        {
        ["id"]=>
        int(127245)
        ["answer"]=>
        string(2) "15"
        ["is_correct"]=>
        bool(false)
        ["sort"]=>
        int(1)
        }
        }
        ["id"]=>
        int(51457)
        ["round_type"]=>
        string(5) "input"
        ["question"]=>
        string(137) "Одной шестнадцетиричной цифрой можно отобразить не больше чем такое число"
        ["timeout"]=>
        int(15)
        ["points"]=>
        int(1)
        ["is_diff_points"]=>
        bool(false)
        ["sort"]=>
        int(1)
        ["question_media"]=>
        string(0) ""
        ["answer_media"]=>
        string(0) ""
        ["answer"]=>
        string(0) ""
         */
    }

    public static function doTestPicture(): void {
        $questionFileName = Paths::WORK_DIR . '/test_question.png';
        $answerFileName   = Paths::WORK_DIR . '/test_answer.png';

        $localTheme = new LocalTheme();
        $localTheme->id   = 1;
        $localTheme->name = "Тестовая тема";

        $localQuestion = new LocalQuestion();
        $localQuestion->id       = 1;
        $localQuestion->question = 'Это довольно длинный тестовый вопрос, включающий в себя деепричастие, причастие, молитву, два пояснения, некоторое уточнение и собственно странно сформулированный вопрос';
        $localQuestion->points   = 1;
        $localQuestion->addAnswer('Все еще неясный ответ');

        $localTheme->questions[] = $localQuestion;

        $pictureBuilder = new PictureBuilder($localTheme, $localQuestion);
        $pictureBuilder->createBothPictures($questionFileName, $answerFileName);
    }

    public static function doPictures(string $gameId): void {
        $questionsFile = Paths::getQuestionFilename($gameId);

        $game = GameDescription::loadFromFile($questionsFile);

        foreach ($game->themes as $localTheme) {
            foreach ($localTheme->questions as $localQuestion) {
                $questionFileName = Paths::getQuestionPictureName($gameId, $localTheme->id, $localQuestion->id);
                $answerFileName   = Paths::getAnswerPictureName($gameId, $localTheme->id, $localQuestion->id);

                try {
                    $pictureBuilder = new PictureBuilder($localTheme, $localQuestion);

                    $pictureBuilder->setRoot = Paths::getSetRoot($gameId);

                    $pictureBuilder->createBothPictures($questionFileName, $answerFileName);
                } catch (\Exception $e) {
                    echo $localTheme->id . '_' . $localQuestion->id . " NOT CREATED: " . $e->getMessage() . "\n";
                }
            }
            echo "Theme {$localTheme->name} processed\n";
        }
    }

    public static function doCheckState(string $gameId): void {
        echo 'Incomplete, need write test - readable files, pictures, sync and so on' . PHP_EOL;
    }

    public static function doCreate(string $gameId): void {
        $questionsFile = Paths::getQuestionFilename($gameId);

        $game = GameDescription::loadFromFile($questionsFile);

        echo PHP_EOL . 'upload start...';
        $client = new ApiClient();
        $num = $client->addGame($game);
        echo 'game num received: ' . $num . PHP_EOL;

        $json = new stdClass();
        $json->gameId = $num;
        $json->questions = [];

        file_put_contents(Paths::getSyncFilename($gameId), json_encode($json));
        echo 'sync file ' . Paths::getSyncFilename($gameId) . ' created' . PHP_EOL;
    }

    public static function doList(): void {
        echo "Incomplete function, need make list local games". PHP_EOL;
    }

    public static function doPrepare(string $gameId): void {
        $inputFileName = Paths::GAMES_DIR . '/' . $gameId . '/ss.txt';
        if (!file_exists($inputFileName)) {
            echo "Cant load file " . $inputFileName . " for gameId = " . $gameId . PHP_EOL;
            return;
        }
        $lines = file($inputFileName);
        $state = 'theme';
        $themes = [];
        $currentQuestion = null;
        $currentTheme    = null;
        $currentGame = new GameDescription($gameId, 'Description');
        foreach($lines as $l) {
            $line = trim($l);
            if ($line == '!') {
                $state = 'theme';
                $currentGame->themes[] = $currentTheme;
                continue;
            }
            $text        = $line;
            $pictureName = null;
            if (strpos($line, '.jpg')) {
                $lastSpace   = strrpos($line, " ");
                $pictureName = trim(substr($line, $lastSpace));
                $text        = trim(str_replace($pictureName, "", $line));
            }

            if ($state == 'theme') {
                $currentTheme = new LocalTheme();
                $currentTheme->id        = count($themes) + 1;
                $currentTheme->name      = $text;
                $currentTheme->questions = [];
                $state = 'question';
            } elseif ($state == 'question') {
                $currentQuestion = new LocalQuestion();
                $currentQuestion->question = $text;
                if ($pictureName) {
                    $currentQuestion->type            = 'picture';
                    $currentQuestion->questionPicture = $pictureName;
                }
                $state = 'answer';
            } elseif ($state == 'answer') {
                if ($currentQuestion === null) {
                    echo "On line " . $line . " answer without question found" . PHP_EOL;
                    return;
                }
                if ($currentQuestion === null) {
                    echo "On line " . $line . " answer without theme found" . PHP_EOL;
                    return;
                }
                if ($line == '') {
                    $currentTheme->questions[] = $currentQuestion;
                    $state = 'question';
                } else {
                    $currentQuestion->addAnswer($text);
                    if ($pictureName) {
                        if ($currentQuestion->type != 'picture') {
                            $currentQuestion->type = 'picture_answer';
                        }
                        $currentQuestion->answerPicture = $pictureName;
                    }
                }
            }
        }
        $currentGame->themes[] = $currentTheme;
        var_dump($currentGame->getAsQuestionJson());
    }
}
