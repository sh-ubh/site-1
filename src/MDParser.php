<?php
namespace Wiki;

use Mni\FrontYAML;

class MDParser implements FrontYAML\Markdown\MarkdownParser {
    public function __construct() {
        $this->mdparser = new WikiParsedown();
    }

    public function parse($markdown) {
        return $this->mdparser->text($markdown);
    }

    public static function factory() {
        return new FrontYAML\Parser(null, new MDParser());
    }
}

class WikiParsedown extends \ParsedownExtra {
    protected function blockHeader($line) {
        $header = parent::blockHeader($line);

        if (!isset($header)) {
            return null;
        }

        $id = preg_replace('/[^a-z0-9]/', '-', strtolower($header['element']['text']));
        $header['element']['attributes']['id'] = $id;

        $header['element']['text'] =
            '<small><a class="text-muted" href="#' . $id . '"><i class="fa fa-link"></i></a></small> '
            . $header['element']['text'];

        return $header;
    }

    protected function blockTable($line, array $block = null) {
        $table = parent::blockTable($line, $block);

        if (!isset($table)) {
            return null;
        }

        $table['element']['attributes']['class'] = "table table-striped";

        return $table;
    }
}