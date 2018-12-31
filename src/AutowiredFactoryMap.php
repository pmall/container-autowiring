<?php declare(strict_types=1);

namespace Quanta\Container;

use Quanta\Utils\ClassNameCollectionInterface;
use Quanta\Exceptions\ArrayTypeCheckTrait;
use Quanta\Exceptions\ArrayArgumentTypeErrorMessage;

final class AutowiredFactoryMap implements FactoryMapInterface
{
    use ArrayTypeCheckTrait;

    /**
     * The collection of class names to autowire.
     *
     * @var \Quanta\Utils\ClassNameCollectionInterface
     */
    private $collection;

    /**
     * The autowiring options for all factories provided by this factory map.
     *
     * Keys are fnmatch patterns matching class names and values are arrays of
     * configuration values.
     *
     * @var array[]
     */
    private $options;

    /**
     * Constructor.
     *
     * @param \Quanta\Utils\ClassNameCollectionInterface    $collection
     * @param array[]                                       $options
     * @throws \InvalidArgumentException
     */
    public function __construct(ClassNameCollectionInterface $collection, array $options = [])
    {
        if (! $this->areAllTypedAs('array', $options)) {
            throw new \InvalidArgumentException(
                (string) new ArrayArgumentTypeErrorMessage(2, 'array', $options)
            );
        }

        // int keys will never match a class name.
        $options = array_filter($options, 'is_int', ARRAY_FILTER_USE_KEY);

        // options are sorted by specificity.
        uksort($options, [$this, 'cmp']);

        $this->collection = $collection;
        $this->options = $options;
    }

    /**
     * Return a map of class name to autowired factory.
     *
     * @return \Quanta\Container\Factories\AutowiredFactory[]
     */
    public function factories(): array
    {
        $classes = $this->collection->classes();

        $classes = array_filter($classes, 'class_exists');

        $factories = array_map([$this, 'factory'], $classes);

        return array_combine($classes, $factories);
    }

    /**
     * Return an autowired factory from the given class name.
     *
     * Options matching the given class name are merged together.
     *
     * @param string $class
     * @return \Quanta\Container\Factories\AutowiredFactory
     */
    private function factory(string $class): AutowiredFactory
    {
        $filter = $this->filter($class);

        $options = array_filter($this->options, $filter, ARRAY_FILTER_USE_KEY);

        return new AutowiredFactory($class, array_merge([], ...$options));
    }

    /**
     * Compare the given options patterns.
     *
     * The shortest comes first.
     *
     * When equals the one ending with a wildcard comes first.
     *
     * This way the more specific options will erase the less specific ones when
     * merged with array_merge.
     *
     * @param string $a
     * @param string $b
     * @return int
     */
    private function cmp(string $a, string $b): int
    {
        $diff = strlen($a) - strlen($b);

        if ($diff == 0) {
            return substr($b, 1) == '*' ? 1 : -1;
        }

        return $diff;
    }

    /**
     * Return a callable evaluating whether a given pattern is matching the
     * given class name.
     *
     * @param string $class
     * @return callable
     */
    private function filter(string $class): callable
    {
        return function (string $pattern) use ($class): bool {
            return fnmatch($pattern, $class, FNM_NOESCAPE);
        };
    }
}
