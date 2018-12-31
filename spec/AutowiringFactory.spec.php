<?php

use Quanta\Utils\Psr4Namespace;
use Quanta\Container\AutowiringFactory;
use Quanta\Container\AutowiredFactoryMap;
use Quanta\Exceptions\ArrayArgumentTypeErrorMessage;

describe('AutowiringFactory', function () {

    context('when there is no default array of options', function () {

        beforeEach(function () {

            $this->factory = new AutowiringFactory;

        });

        describe('->__invoke()', function () {

            context('when no array of options is given', function () {

                it('should return a new AutowiredFactoryMap using the given namespace and directory and an empty array of options', function () {

                    $test = ($this->factory)('Namespace', '/some/directory');

                    expect($test)->toEqual(new AutowiredFactoryMap(
                        new Psr4Namespace('Namespace', '/some/directory'), []
                    ));

                });

            });

            context('when an array of options is given', function () {

                context('when the given array of options contains only arrays', function () {

                    it('should return a new AutowiredFactoryMap using the given namespace, directory and array of options', function () {

                        $options = [
                            SomeClass1::class => ['$a' => 'a'],
                            SomeClass2::class => ['$a' => 'a'],
                            SomeClass3::class => ['$a' => 'a'],
                        ];

                        $test = ($this->factory)('Namespace', '/some/directory', $options);

                        expect($test)->toEqual(new AutowiredFactoryMap(
                            new Psr4Namespace('Namespace', '/some/directory'), $options
                        ));

                    });

                });

                context('when the given array of options does not contains only arrays', function () {

                    it('should throw an InvalidArgumentException', function () {

                        ArrayArgumentTypeErrorMessage::testing();

                        $options = [
                            SomeClass1::class => ['$a' => 'a'],
                            SomeClass2::class => 'options',
                            SomeClass3::class => ['$a' => 'a'],
                        ];

                        $test = function () use ($options) {
                            ($this->factory)('Namespace', '/some/directory', $options);
                        };

                        expect($test)->toThrow(new InvalidArgumentException(
                            (string) new ArrayArgumentTypeErrorMessage(3, 'array', $options)
                        ));

                    });

                });

            });

        });

    });

    context('when there is a default array of options', function () {

        context('when the default array of options contains only arrays', function () {

            beforeEach(function () {

                $this->options = [
                    SomeClass1::class => ['$a' => 'a1', '$b' => 'b1', '$c' => ['k1' => 'c11', 'k2' => 'c12']],
                    SomeClass2::class => ['$a' => 'a1', '$b' => 'b1', '$c' => ['k1' => 'c11', 'k2' => 'c12']],
                    SomeClass3::class => ['$a' => 'a1', '$b' => 'b1', '$c' => ['k1' => 'c11', 'k2' => 'c12']],
                ];

                $this->factory = new AutowiringFactory($this->options);

            });

            describe('->__invoke()', function () {

                context('when no array of options is given', function () {

                    it('should return a new AutowiredFactoryMap using the given namespace, directory and the default array of options', function () {

                        $test = ($this->factory)('Namespace', '/some/directory');

                        expect($test)->toEqual(new AutowiredFactoryMap(
                            new Psr4Namespace('Namespace', '/some/directory'), $this->options
                        ));

                    });

                });

                context('when an array of options is given', function () {

                    context('when the given array of options contains only arrays', function () {

                        it('should return a new AutowiredFactoryMap using the given namespace, directory and array of options completed with the default array of options', function () {

                            $options = [
                                SomeClass1::class => ['$b' => 'b2', '$c' => ['k1' => 'c21']],
                                SomeClass3::class => ['$b' => 'b2', '$c' => ['k1' => 'c21']],
                                SomeClass4::class => ['$b' => 'b2', '$c' => ['k1' => 'c21']],
                            ];

                            $test = ($this->factory)('Namespace', '/some/directory', $options);

                            $expected = new AutowiredFactoryMap(
                                new Psr4Namespace('Namespace', '/some/directory'), [
                                    SomeClass1::class => ['$a' => 'a1', '$b' => 'b2', '$c' => ['k1' => 'c21']],
                                    SomeClass2::class => ['$a' => 'a1', '$b' => 'b1', '$c' => ['k1' => 'c11', 'k2' => 'c12']],
                                    SomeClass3::class => ['$a' => 'a1', '$b' => 'b2', '$c' => ['k1' => 'c21']],
                                    SomeClass4::class => ['$b' => 'b2', '$c' => ['k1' => 'c21']],
                                ]
                            );

                            expect($test)->toEqual($expected);

                        });

                    });

                    context('when the given array of options does not contain only arrays', function () {

                        it('should throw an InvalidArgumentException', function () {

                            ArrayArgumentTypeErrorMessage::testing();

                            $options = [
                                SomeClass1::class => ['$a' => 'a'],
                                SomeClass2::class => 'options',
                                SomeClass3::class => ['$a' => 'a'],
                            ];

                            $test = function () use ($options){
                                ($this->factory)('Namespace', '/some/directory', $options);
                            };

                            expect($test)->toThrow(new InvalidArgumentException(
                                (string) new ArrayArgumentTypeErrorMessage(3, 'array', $options)
                            ));

                        });

                    });

                });

            });

        });

        context('when the default array of options does not contain only arrays', function () {

            it('should throw an InvalidArgumentException', function () {

                ArrayArgumentTypeErrorMessage::testing();

                $options = [
                    SomeClass1::class => ['$a' => 'a'],
                    SomeClass2::class => 'options',
                    SomeClass3::class => ['$a' => 'a'],
                ];

                $test = function () use ($options){
                    new AutowiringFactory($options);
                };

                expect($test)->toThrow(new InvalidArgumentException(
                    (string) new ArrayArgumentTypeErrorMessage(1, 'array', $options)
                ));

            });

        });

    });

});
