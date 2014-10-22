<?php

namespace spec\SMGladkovskiy\Querier\Console;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Illuminate\Filesystem\Filesystem;
use Mustache_Engine;
use SMGladkovskiy\Querier\Console\QueryInput;

class QueryGeneratorSpec extends ObjectBehavior {

    function let(Filesystem $file, Mustache_Engine $mustache)
    {
        $this->beConstructedWith($file, $mustache);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('SMGladkovskiy\Querier\Console\QueryGenerator');
    }

    function it_generates_a_command_class(Filesystem $file, Mustache_Engine $mustache)
    {
        $input = new QueryInput('SomeQuery', 'Acme\Bar', ['name', 'email'], '$name, $email');
        $template = 'foo.stub';
        $destination = 'app/Acme/Bar/SomeQuery.php';

        $file->get($template)->shouldBeCalled()->willReturn('template');
        $mustache->render('template', $input)->shouldBeCalled()->willReturn('stub');
        $file->put($destination, 'stub')->shouldBeCalled();

        $this->make($input, $template, $destination);
    }

}
