<?php
declare(strict_types = 1);
namespace Inspire\Validator;

use Respect\Validation\ {
    Validator,
    Factory
};
use Respect\Validation\Exceptions\ComponentException;

/**
 * Description of Variable
 *
 * @author aalves
 */
class Variable
{

    private static ?Validator $instance = null;

    /**
     * Create instance validator.
     */
    public static function create(): Validator
    {
        self::$instance = new Validator();
        return self::$instance;
    }

    /**
     *
     * {@inheritdoc}
     */
    public function check($input): void
    {
        self::$instance->check($input);
    }

    /**
     * Creates a new Validator instance with a rule that was called on the static method.
     *
     * @param mixed[] $arguments
     *
     * @throws ComponentException
     */
    public static function __callStatic(string $ruleName, array $arguments): bool
    {
        $rule = Variable::create()->__call($ruleName, $arguments[1] ?? []);
        return call_user_func_array([
            $rule,
            'validate'
        ], $arguments);
    }

    /**
     * Create a new rule by the name of the method and adds the rule to the chain.
     *
     * @param mixed[] $arguments
     *
     * @throws ComponentException
     */
    public function __call(string $ruleName, array $arguments): Validator
    {
        self::$instance->addRule(Factory::getDefaultInstance()->rule($ruleName, $arguments));
        return self::$instance;
    }
}

