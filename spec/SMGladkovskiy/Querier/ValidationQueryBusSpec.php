<?php

namespace spec\SMGladkovskiy\Querier;

use Illuminate\Foundation\Application;
use SMGladkovskiy\Querier\QueryBus;
use SMGladkovskiy\Querier\QueryTranslator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ValidationQueryBusSpec extends ObjectBehavior
{
    function let(QueryBus $bus, Application $application, QueryTranslator $translator)
    {
        $this->beConstructedWith($bus, $application, $translator);
    }

    function it_does_not_handle_command_if_validation_fails(
        Application $application,
        QueryTranslator $translator,
        QueryBus $bus,
        ExampleQuery $query,
        ExampleValidator $validator
    ) {
        // Own responsibility
        $translator->toValidator($query)->willReturn(ExampleValidator::class);
        $application->make(ExampleValidator::class)->willReturn($validator);
        $validator->validate($query)->willThrow('RuntimeException');

        // Delegated responsibility
        $bus->executeQuery($query)->shouldNotBeCalled();

        $this->shouldThrow('RuntimeException')->duringExecuteQuery($query);
    }

    function it_handles_command_if_validation_succeeds(
        Application $application,
        QueryTranslator $translator,
        QueryBus $bus,
        ExampleQuery $query,
        ExampleValidator $validator
    ) {
        // Own responsibility
        $translator->toValidator($query)->willReturn(ExampleValidator::class);
        $application->make(ExampleValidator::class)->willReturn($validator);

        // Delegated responsibility
        $bus->executeQuery($query)->shouldBeCalled();

        $this->executeQuery($query);
    }
}

// Stub Stuff
class ExampleQuery {}
class ExampleValidator { public function validate($query) {} }

namespace Illuminate\Foundation;
class Application { function make() {} }