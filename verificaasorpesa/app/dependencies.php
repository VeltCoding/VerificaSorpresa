<?php

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;


return function (ContainerBuilder $containerBuilder) {

    $containerBuilder->addDefinitions([

        PDO::class => function (ContainerInterface $c) {

            return new PDO(
                "mysql:host=localhost;dbname=verificaasorpresa;charset=utf8mb4",
                "root",
                "",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );

        },

    ]);

};