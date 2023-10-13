<?php

use Hyvor\FeedParser\Parser\AtomParser;

it('runs', function() {

    $input = file_get_contents(__DIR__ . '/data/atom.input.atom');
    $parser = new AtomParser($input);
    $parser->parse();

});
