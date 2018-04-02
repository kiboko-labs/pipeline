<?php

namespace Kiboko\Component\Phroovy\AST\TreeResolution;

use Kiboko\Component\Phroovy\AST\Exception;
use Kiboko\Component\Phroovy\AST\Node;
use Kiboko\Component\Phroovy\AST\TokenConstraint;
use Kiboko\Component\Phroovy\AST\TokenStream;

class PostActionStepCollectionResolution implements TreeResolutionInterface
{
    /**
     * @var StepResolution
     */
    private $stepResolution;

    /**
     * @param StepResolution $stepResolution
     */
    public function __construct(StepResolution $stepResolution)
    {
        $this->stepResolution = $stepResolution;
    }

    public function assert(TokenStream $tokenStream): bool
    {
        return $tokenStream->assertAny(TokenConstraint::anyKeyword('always', 'unstable', 'success', 'failure', 'changed'));
    }

    /**
     * @param TokenStream $tokenStream
     *
     * @return Node\StageNode
     */
    public function create(TokenStream $tokenStream): Node\NodeInterface
    {
        $tokenStream->expectAny(TokenConstraint::anyKeyword('always', 'unstable', 'success', 'failure', 'changed'));
        $tokenStream->expect(TokenConstraint::openingCurlyBraces());

        $stepCollection = new Node\StepCollectionNode();

        while (true) {
            $stepCollection->append($this->stepResolution->create($tokenStream));

            if ($tokenStream->assert(TokenConstraint::closingCurlyBraces())) {
                $tokenStream->step();
                break;
            }
        }

        return $stepCollection;
    }
}