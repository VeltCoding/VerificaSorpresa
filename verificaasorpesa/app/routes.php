<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


return function (App $app) {

    // Helper JSON
    $json = function (Response $response, $data) {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    };

    // Q1 - pnome dei pezzi per cui esiste un qualche fornitore
    $app->get('/q1', function (Request $request, Response $response) use ($json) {
        $pdo = $this->get(PDO::class);
        $stmt = $pdo->query("
            SELECT DISTINCT p.pnome
            FROM Pezzi p
            JOIN Catalogo c ON c.pid = p.pid
        ");
        return $json($response, $stmt->fetchAll());
    });

    // Q2 - fornitori che forniscono ogni pezzo
    $app->get('/q2', function (Request $request, Response $response) use ($json) {
        $pdo = $this->get(PDO::class);
        $stmt = $pdo->query("
            SELECT f.fnome
            FROM Fornitori f
            JOIN Catalogo c ON c.fid = f.fid
            GROUP BY f.fid
            HAVING COUNT(DISTINCT c.pid) =
                (SELECT COUNT(*) FROM Pezzi)
        ");
        return $json($response, $stmt->fetchAll());
    });

    // Q3 - fornitori che forniscono tutti i pezzi di un colore (parametro colore)
    $app->get('/q3', function (Request $request, Response $response) use ($json) {
        $pdo = $this->get(PDO::class);
        $params = $request->getQueryParams();
        $colore = $params['colore'] ?? 'rosso';

        $stmt = $pdo->prepare("
            SELECT f.fnome
            FROM Fornitori f
            JOIN Catalogo c ON c.fid = f.fid
            JOIN Pezzi p ON p.pid = c.pid
            WHERE p.colore = :colore
            GROUP BY f.fid
            HAVING COUNT(DISTINCT p.pid) =
                (SELECT COUNT(*) FROM Pezzi WHERE colore = :colore)
        ");

        $stmt->execute(['colore' => $colore]);
        return $json($response, $stmt->fetchAll());
    });

    // Q4 - pezzi forniti da un solo fornitore specifico (parametro nome)
    $app->get('/q4', function (Request $request, Response $response) use ($json) {
        $pdo = $this->get(PDO::class);
        $params = $request->getQueryParams();
        $nome = $params['nome'] ?? 'Acme';

        $stmt = $pdo->prepare("
            SELECT p.pnome
            FROM Pezzi p
            JOIN Catalogo c ON c.pid = p.pid
            JOIN Fornitori f ON f.fid = c.fid
            GROUP BY p.pid
            HAVING SUM(f.fnome = :nome) > 0
               AND COUNT(DISTINCT c.fid) = 1
        ");

        $stmt->execute(['nome' => $nome]);
        return $json($response, $stmt->fetchAll());
    });

    // Q5 - fornitori che vendono un pezzo sopra il costo medio di quel pezzo
    $app->get('/q5', function (Request $request, Response $response) use ($json) {
        $pdo = $this->get(PDO::class);
        $stmt = $pdo->query("
            SELECT DISTINCT fid
            FROM Catalogo c1
            WHERE costo >
                (SELECT AVG(costo)
                 FROM Catalogo c2
                 WHERE c2.pid = c1.pid)
        ");
        return $json($response, $stmt->fetchAll());
    });

    // Q6 - per ciascun pezzo, fornitore che lo vende al costo massimo
    $app->get('/q6', function (Request $request, Response $response) use ($json) {
        $pdo = $this->get(PDO::class);
        $stmt = $pdo->query("
            SELECT p.pid, p.pnome, f.fnome, c.costo
            FROM Catalogo c
            JOIN Pezzi p ON p.pid = c.pid
            JOIN Fornitori f ON f.fid = c.fid
            WHERE c.costo = (
                SELECT MAX(c2.costo)
                FROM Catalogo c2
                WHERE c2.pid = c.pid
            )
        ");
        return $json($response, $stmt->fetchAll());
    });

    // Q7 - fornitori che forniscono solo pezzi rossi
    $app->get('/q7', function (Request $request, Response $response) use ($json) {
        $pdo = $this->get(PDO::class);
        $stmt = $pdo->query("
            SELECT c.fid
            FROM Catalogo c
            JOIN Pezzi p ON p.pid = c.pid
            GROUP BY c.fid
            HAVING SUM(p.colore <> 'rosso') = 0
        ");
        return $json($response, $stmt->fetchAll());
    });

    // Q8 - fornitori che forniscono un pezzo rosso e uno verde
    $app->get('/q8', function (Request $request, Response $response) use ($json) {
        $pdo = $this->get(PDO::class);
        $stmt = $pdo->query("
            SELECT c.fid
            FROM Catalogo c
            JOIN Pezzi p ON p.pid = c.pid
            GROUP BY c.fid
            HAVING SUM(p.colore = 'rosso') > 0
               AND SUM(p.colore = 'verde') > 0
        ");
        return $json($response, $stmt->fetchAll());
    });

    // Q9 - fornitori che forniscono un pezzo rosso o verde
    $app->get('/q9', function (Request $request, Response $response) use ($json) {
        $pdo = $this->get(PDO::class);
        $stmt = $pdo->query("
            SELECT DISTINCT c.fid
            FROM Catalogo c
            JOIN Pezzi p ON p.pid = c.pid
            WHERE p.colore IN ('rosso','verde')
        ");
        return $json($response, $stmt->fetchAll());
    });

    // Q10 - pezzi forniti da almeno due fornitori
    $app->get('/q10', function (Request $request, Response $response) use ($json) {
        $pdo = $this->get(PDO::class);
        $stmt = $pdo->query("
            SELECT pid
            FROM Catalogo
            GROUP BY pid
            HAVING COUNT(DISTINCT fid) >= 2
        ");
        return $json($response, $stmt->fetchAll());
    });

};