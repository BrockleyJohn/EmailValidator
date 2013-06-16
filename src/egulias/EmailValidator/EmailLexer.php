<?php

namespace egulias\EmailValidator;

use Doctrine\Common\Lexer;

class EmailLexer extends Lexer
{
    //ASCII values
    const C_DEL              = 127;
    const C_NUL              = 0;
    const S_AT               = 64;//'@';
    const S_BACKSLASH        = 92;//'\\';
    const S_DOT              = 46;//'.';
    const S_DQUOTE           = 34;//'"';
    const S_OPENPARENTHESIS  = 49;//'(';
    const S_CLOSEPARENTHESIS = 261;//')';
    const S_OPENBRACKET      = 262;//'[';
    const S_CLOSEBRACKET     = 263;//']';
    const S_HYPHEN           = 264;//'-';
    const S_COLON            = 265;//':';
    const S_DOUBLECOLON      = 266;//'::';
    const S_SP               = 267;//' ';
    const S_HTAB             = 268;//"\t";
    const S_CR               = 269;//"\r";
    const S_LF               = 270;//"\n";
    const S_IPV6TAG          = 271;//'IPv6:';
    const S_LOWERTHAN        = 272;//'<'
    const S_GREATERTHAN      = 273;//'>'
    const S_COMMA            = 274;//','
    const S_SEMICOLON        = 275;//';'
    const S_OPENQBRACKET     = 276;//'[';
    const S_CLOSEQBRACKET    = 277;//']';
    const S_EMPTY            = null;//'';
    const GENERIC            = 300;
    const CRLF               = 301;
    const INVALID            = 302;

    /**
     * US-ASCII visible characters not valid for atext (@link http://tools.ietf.org/html/rfc5322#section-3.2.3)
     *
     * @var array
     */
    protected $charValue = array(
        '('    => self::S_OPENPARENTHESIS,
        ')'    => self::S_CLOSEPARENTHESIS,
        '<'    => self::S_LOWERTHAN,
        '>'    => self::S_GREATERTHAN,
        '['    => self::S_OPENBRACKET,
        ']'    => self::S_CLOSEBRACKET,
        ':'    => self::S_COLON,
        ';'    => self::S_SEMICOLON,
        '@'    => self::S_AT,
        '\\'   => self::S_BACKSLASH,
        ','    => self::S_COMMA,
        '.'    => self::S_DOT,
        '"'    => self::S_DQUOTE,
        '-'    => self::S_HYPHEN,
        '::'   => self::S_DOUBLECOLON,
        ' '    => self::S_SP,
        "\t"   => self::S_HTAB,
        "\r"   => self::S_CR,
        "\n"   => self::S_LF,
        "\r\n" => self::CRLF,
        'IPv6' => self::S_IPV6TAG,
        '<'    => self::S_LOWERTHAN,
        '>'    => self::S_GREATERTHAN,
        '{'    => self::S_OPENQBRACKET,
        '}'    => self::S_CLOSEQBRACKET,
        ''     => self::S_EMPTY
    );

    protected $previous;

    /**
     * getName
     *
     * @param string $type
     *
     * @return string $name
     * @throws \InvalidArgumentException
     */
    public function getName($type)
    {
        $ref = new \ReflectionClass($this);
        foreach ($ref->getConstants() as $name => $value) {
            if ($value === $type) {
                return $name;
            }
        }

        throw new \InvalidArgumentException(sprintf('There is no token with value %s.', json_encode($type)));
    }

    public function find($type)
    {
        $search = clone $this;
        $search->skipUntil($type);

        if (!$search->lookahead) {
            throw new \UnexpectedValueException($type . ' not found');
        }

    }

    /**
     * getPrevious
     *
     * @return array token
     */
    public function getPrevious()
    {
        return $this->previous;
    }

    /**
     * moveNext
     *
     * @return mixed
     */
    public function moveNext()
    {
        $this->previous = $this->token;

        return parent::moveNext();
    }

    /**
     * Lexical catchable patterns.
     *
     * @return array
     */
    protected function getCatchablePatterns()
    {
        return array(
            '[a-zA-Z_]+[4,6]?',
            '[0-9]+',
            '\r\n',
            '::',
            '\s+',
            '[\x1-\x1F]+',
            '.'
            );
    }

    /**
     * Lexical non-catchable patterns.
     *
     * @return array
     */
    protected function getNonCatchablePatterns()
    {
        return array('[\x7f-\xff]+');
    }

    /**
     * Retrieve token type. Also processes the token value if necessary.
     *
     * @param string $value
     * @throws \InvalidArgumentException
     * @return integer
     */
    protected function getType(&$value)
    {
        if (isset($this->charValue[$value])) {
            return $this->charValue[$value];
        }

        if (preg_match('/[\x1-\x1F]+/', $value)) {
            return self::INVALID;
        }

        if (preg_match('/[\x7f-\xff]+/', $value)) {
            throw new \InvalidArgumentException(sprintf('There is no token with value %s.', json_encode($value)));
        }

        return  self::GENERIC;
    }
}
