<?php
 $forbiddenChars = array('&', '$', '<', '>', '{', '}', '\\', ';', '!', '?', '[', ']');
            foreach ($_POST as $key => $value) {
                if ($key !== 'password') {
                    foreach ($forbiddenChars as $char) {
                        if (strpos($value, $char) !== false) {
                            echo json_encode(['status' => false, 'message' => "Forbidden character detected in $key input."]);
                            exit;
                        }
                    }
                }
            }