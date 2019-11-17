<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Builder;

use RstGroup\ObjectBuilder\Builder;

final class Blueprint implements Builder
{
    /** @var Builder\Blueprint\Factory */
    private $blueprintFactory;
    /** @var ParameterNameStrategy */
    private $strategy;

    /** @codeCoverageIgnore */
    public function __construct(
        Builder\Blueprint\Factory $factory,
        ParameterNameStrategy $strategy
    ) {
        $this->blueprintFactory = $factory;
        $this->strategy = $strategy;
    }

    /** @param mixed[] $data */
    public function build(string $class, array $data): object
    {
        $blueprint = $this->blueprintFactory->create($class);

        $preparedData = $this->prepareData($data);

        return $blueprint($preparedData);
    }

    /**
     * @param mixed[] $data
     * @return mixed[]
     */
    private function prepareData(array $data): array
    {
        $preparedData = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = $this->prepareData($value);
            }

            if (!is_int($key)) {
                $key = $this->strategy->getName($key);
            }

            $preparedData[$key] = $value;
        }

        return $preparedData;
    }
}
