<?php

/**
 * Паттерн Facade
 * Скрывает подробности реализации (взаимосвязи, зависимости и т.д.) в рамках одного
 * относительно простого (как правило, статичного) класса
 */


// Интерфейс парсера текста
interface Parser
{
    public function parse(string $text) : string;
}

// Частные парсеры

class stripTagsParser implements Parser
{
    public function parse(string $text): string
    {
        return strip_tags($text);
    }
}

class removeSpacesParser implements Parser
{
    public function parse(string $text): string
    {
        return str_replace(' ', '', $text);
    }
}

// Аналогично
// class replaceSpacesToEolParser implements Parser {}
// class htmlspecialcharsParser implements Parser {}
// class removeSymbolsParser implements Parser {}
// class toNumberParser implements Parser {}
//  и т.д.


class Facade
{
    public static function parse($input)
    {
        $data = json_decode($input, true);
        $text = $data['job']['text'];
        $methods = $data['job']['methods'];

        foreach ($methods as $method) {
            $parserClass = $method . 'Parser';
            // TODO: здесь можно проверять существование класса парсера, к примеру, с помощью is_a()
            $parser = new $parserClass();
            /** @var Parser $parser */
            $text = $parser->parse($text);
        }

        return $text;
    }
}


$input = <<<JSON
{
    "job": {
        "text": "Привет, мне на <a href=\"test@test.ru\">test@test.ru</a> пришло приглашение встретиться, попить кофе с <strong>10%</strong> содержанием молока за <i>$5</i>, пойдем вместе!",
        "methods": [
            "stripTags", "removeSpaces", "replaceSpacesToEol", "htmlspecialchars", "removeSymbols", "toNumber"
        ]
    }
}
JSON;

echo Facade::parse($input);

// Масштабируемость обеспечивается за счет создания классов, имплементирующих интерфейс Parser
