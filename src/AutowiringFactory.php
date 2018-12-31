<?php declare(strict_types=1);

namespace Quanta\Container;

use Quanta\Utils\Psr4Namespace;
use Quanta\Exceptions\ArrayTypeCheckTrait;
use Quanta\Exceptions\ArrayArgumentTypeErrorMessage;

final class AutowiringFactory
{
    use ArrayTypeCheckTrait;

    /**
     * The default autowiring options for all produced autowired factory maps.
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
     * @param array[] $options
     * @throws \InvalidArgumentException
     */
    public function __construct(array $options = [])
    {
        if (! $this->areAllTypedAs('array', $options)) {
            throw new \InvalidArgumentException(
                (string) new ArrayArgumentTypeErrorMessage(1, 'array', $options)
            );
        }

        $this->options = $options;
    }

    /**
     * Return a new autowired factory map from the given namespace, directory
     * and options.
     *
     * Default options are added to the given options.
     *
     * @param string $namespace
     * @param string $directory
     * @param array[] $options
     * @return \Quanta\Container\AutowiredFactoryMap
     * @throws \InvalidArgumentException
     */
    public function __invoke(string $namespace, string $directory, array $options = []): AutowiredFactoryMap
    {
        $collection = new Psr4Namespace($namespace, $directory);

        foreach ($this->options as $pattern => $config) {
            if (is_array($options[$pattern] ?? [])) {
                $options[$pattern] = ($options[$pattern] ?? []) + $config;
            }
        }

        try {
            return new AutowiredFactoryMap($collection, $options);
        }

        catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException(
                (string) new ArrayArgumentTypeErrorMessage(3, 'array', $options)
            );
        }
    }
}
