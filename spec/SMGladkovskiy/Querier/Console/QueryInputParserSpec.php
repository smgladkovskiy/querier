<?php namespace spec\Acme\Console;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class QueryInputParserSpec extends ObjectBehavior {

    function it_is_initializable()
    {
        $this->shouldHaveType('Acme\Console\QueryInputParser');
    }

    function it_returns_an_instance_of_command_input()
    {
        $this->parse('Foo/Bar/MyQuery', 'username, email')
            ->shouldBeAnInstanceOf('Acme\Console\QueryInput');
    }

    function it_parses_the_name_of_the_class()
    {
        $input = $this->parse('Foo/Bar/MyQuery', 'username, email');

        $input->name->shouldBe('MyQuery');
    }

    function it_parses_the_namespace_of_the_class()
    {
        $input = $this->parse('Foo/Bar/MyQuery', 'username, email');

        $input->namespace->shouldBe('Foo\Bar');
    }

    function it_parses_the_properties_for_the_class()
    {
        $input = $this->parse('Foo/Bar/MyQuery', 'username, email');

        $input->properties->shouldBe(['username', 'email']);
    }


}
