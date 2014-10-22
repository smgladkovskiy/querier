<?php

$saveDir = './tests/acceptance/tmp';
$stubDir = './tests/acceptance/stubs';
$queryToGenerate = 'FooQuery';

$I = new AcceptanceTester($scenario);
$I->wantTo('generate a query and handler class');

$I->runShellCommand("php ../../../artisan querier:generate $queryToGenerate --properties='bar, baz' --base='$saveDir'");
$I->seeInShellOutput('All done!');

// My Command stub should match the generated class.
$I->openFile("{$saveDir}/{$queryToGenerate}.php");
$I->seeFileContentsEqual(file_get_contents("{$stubDir}/{$queryToGenerate}.stub"));

// And my QueryHandler stub should match its generated counterpart, as well.
$I->openFile("{$saveDir}/{$queryToGenerate}Handler.php");
$I->seeFileContentsEqual(file_get_contents("{$stubDir}/{$queryToGenerate}Handler.stub"));

$I->cleanDir($saveDir);


