<?php

$version = '1.6.2';

$sql[] = '
ALTER TABLE instance 
ADD COLUMN custom_file
VARCHAR(255)
NULL
AFTER custom_remote_path
;
';