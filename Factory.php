<?php

/**
 * Паттерн Factory
 * Создает (и при необходимости инициализирует) объекты нужного типа без явного указания типа (через идентификаторы)
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


class Factory
{
    /**
     * @param string $name
     * @return Parser
     */
    public static function createParser(string $name) : Parser
    {
        $parserClass = $name . 'Parser';
        // TODO: здесь можно проверять существование класса парсера
        return new $parserClass();
    }
}


// Unit-тест
function factoryTest(string $input)
{
    $data = json_decode($input, true);
    $text = $data['job']['text'];
    $methods = $data['job']['methods'];

    foreach ($methods as $method) {
        $parser = Factory::createParser($method);
        $text = $parser->parse($text);
    }

    return $text;
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

echo factoryTest($input);

// Масштабируемость обеспечивается за счет создания классов, наследуемых от класса Parser
