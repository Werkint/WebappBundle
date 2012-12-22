<?php
namespace Werkint\Bundle\WebappBundle\Twig\Extension;

use \Twig_Node_Module,
    \Twig_Compiler,
    \Twig_Node_Expression_Constant;

class NodeModule extends Twig_Node_Module
{

    public static function nest(Twig_Node_Module $node)
    {
        $node = new static(
            $node->getNode('body'),
            $node->getNode('parent'),
            $node->getNode('blocks'),
            $node->getNode('macros'),
            $node->getNode('traits'),
            $node->getAttribute('embedded_templates'),
            $node->getAttribute('filename')
        );
        return $node;
    }

    protected function compileConstructor(Twig_Compiler $compiler)
    {
        $compiler
            ->write("public function __construct(Twig_Environment \$env)\n", "{\n")
            ->indent()
            ->write("parent::__construct(\$env);\n\n");

        // parent
        if (null === $this->getNode('parent')) {
            $compiler->write("\$this->parent = false;\n\n");
        } elseif ($this->getNode('parent') instanceof Twig_Node_Expression_Constant) {
            $compiler
                ->write("\$this->parent = \$this->env->loadTemplate(")
                ->subcompile($this->getNode('parent'))
                ->raw(");\n\n");
        }

        $countTraits = count($this->getNode('traits'));
        if ($countTraits) {
            // traits
            foreach ($this->getNode('traits') as $i => $trait) {
                $this->compileLoadTemplate($compiler, $trait->getNode('template'), sprintf('$_trait_%s', $i));

                $compiler
                    ->addDebugInfo($trait->getNode('template'))
                    ->write(sprintf("if (!\$_trait_%s->isTraitable()) {\n", $i))
                    ->indent()
                    ->write("throw new Twig_Error_Runtime('Template \"'.")
                    ->subcompile($trait->getNode('template'))
                    ->raw(".'\" cannot be used as a trait.');\n")
                    ->outdent()
                    ->write("}\n")
                    ->write(sprintf("\$_trait_%s_blocks = \$_trait_%s->getBlocks();\n\n", $i, $i));

                foreach ($trait->getNode('targets') as $key => $value) {
                    $compiler
                        ->write(sprintf("\$_trait_%s_blocks[", $i))
                        ->subcompile($value)
                        ->raw(sprintf("] = \$_trait_%s_blocks[", $i))
                        ->string($key)
                        ->raw(sprintf("]; unset(\$_trait_%s_blocks[", $i))
                        ->string($key)
                        ->raw("]);\n\n");
                }
            }

            if ($countTraits > 1) {
                $compiler
                    ->write("\$this->traits = array_merge(\n")
                    ->indent();

                for ($i = 0; $i < $countTraits; $i++) {
                    $compiler
                        ->write(sprintf("\$_trait_%s_blocks" . ($i == $countTraits - 1 ? '' : ',') . "\n", $i));
                }

                $compiler
                    ->outdent()
                    ->write(");\n\n");
            } else {
                $compiler
                    ->write("\$this->traits = \$_trait_0_blocks;\n\n");
            }

            $compiler
                ->write("\$this->blocks = array_merge(\n")
                ->indent()
                ->write("\$this->traits,\n")
                ->write("array(\n");
        } else {
            $compiler
                ->write("\$this->blocks = array(\n");
        }

        // blocks
        $compiler
            ->indent();

        foreach ($this->getNode('blocks') as $name => $node) {
            $compiler
                ->write(sprintf("'%s' => array(\$this, 'block_%s'),\n", $name, $name));
        }

        if ($countTraits) {
            $compiler
                ->outdent()
                ->write(")\n");
        }

        $compiler
            ->outdent()
            ->write(");\n");

        $compiler
            ->write("\n// Callback to template hook, it adds js/scss scripts\n")
            ->write('if(method_exists($this,\'postConstruct\')) {' . "\n")
            ->indent()
            ->write('$this->postConstruct();' . "\n")
            ->outdent()
            ->write('}' . "\n");

        $compiler
            ->outdent()
            ->write("}\n\n");
    }
}
