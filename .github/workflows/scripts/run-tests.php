#!/usr/bin/env php
<?php

$dirs = array_filter(glob('*'), 'is_dir');
$testHelper = new TestHelper();

foreach ($dirs as $dir) {
    $testHelper->testDirectory($dir);
}

$errors = $testHelper->errors;
if (count($errors) > 0) {
    foreach($errors as $dir => $scriptErrors) {
        print "Directory '$dir'\n";

        foreach($scriptErrors as $error) {
            print "  $error\n";
        }
    }

    exit(1);
}

print "No errors were found\n";
exit(0);

class TestHelper {
    public $errors = [];

    /**
     * Tests the files in a directory
     */
    public function testDirectory($dir) {
        $errors = [];

        if (preg_match('/[^a-z0-9\-]/', $dir)) {
            $errors[] = "Invalid characters were found in directory name '$dir'!";
        }

        $jsonData = file_get_contents($dir . "/info.json");
        $data = json_decode($jsonData, true);

        if ($data["name"] == "") {
            $errors[] = "No name was entered!";
        }

        $identifier = $data["identifier"];
        if ($identifier == "") {
            $errors[] = "No identifier was entered!";
        } elseif (preg_match('/[^a-z0-9\-]/', $identifier)) {
            $errors[] = "Invalid characters were found in identifier '$identifier'!";
        }

        if ($identifier != $dir) {
            $errors[] = "Identifier and directory name do not match!";
        }

        if ($data["description"] == "") {
            $errors[] = "No description was entered!";
        }

        $script = $data["script"];
        if ($script == "") {
            $errors[] = "No script was entered!";
        } elseif (!file_exists($dir . "/" . $script)) {
            $errors[] = "Script '$script' doesn't exist!";
        }

        if ($data["version"] == "") {
            $errors[] = "No version was entered!";
        }

        if (isset($data["platforms"])) {
            if (!is_array($data["platforms"])) {
                $errors[] = "'platforms' has to be an array!";
            } else {
                foreach ($data["platforms"] as $platform) {
                    if (!in_array($platform, array("linux", "macos", "windows"))) {
                        $errors[] = "Unsupported platform '$platform', only 'linux', 'macos' and 'windows' are allowed!";
                    }
                }
            }
        }

        if (count($errors) > 0) {
            $this->errors[$dir] = $errors;
        }
    }
}
