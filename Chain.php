<?php

/**
 * Паттерн Chain of Command
 * Реализует набор команд, которые последовательно вызываются
 */


// Абстрактный класс парсера текста
abstract class Parser
{
    /** @var Parser */
    protected $next;

    public function setNext(Parser $parser)
    {
        $this->next = $parser;
        return $this;
    }

    public function parse(string $text) : string
    {
        $text = $this->internalParse($text);
        return $this->next ? $this->next->parse($text) : $text;
    }

    abstract protected function internalParse(string $text): string;
}

// Частные парсеры

class stripTagsParser extends Parser
{
    public function internalParse(string $text): string
    {
        return strip_tags($text);
    }
}

class removeSpacesParser extends Parser
{
    public function internalParse(string $text): string
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


// Unit-тест
function chainTest(string $input)
{
    $data = json_decode($input, true);
    $text = $data['job']['text'];
    $methods = $data['job']['methods'];

    $method = $methods[0];
    $parserClass = $method . 'Parser';
    /** @var Parser $parser */
    $parser = new $parserClass();
    $first = $parser;

    // Формируем цепочку парсеров
    for ($i = 1; $i < count($methods); $i++) {
        $method = $methods[$i];
        $parserClass = $method . 'Parser';
        $nextParser = new $parserClass();
        $parser->setNext($nextParser);
        $parser = $nextParser;
    }

    // После запуска первого парсера последующие запустяться автоматически
    return $first->parse($text);

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

echo chainTest($input);

// Масштабируемость обеспечивается за счет создания классов, наследуемых от класса Parser
