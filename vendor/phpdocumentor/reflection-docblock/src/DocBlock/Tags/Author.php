<?php


namespace phpDocumentor\Reflection\DocBlock\Tags;

use Webmozart\Assert\Assert;


final class Author extends BaseTag implements Factory\StaticMethod
{
    
    protected $name = 'author';

    
    private $authorName = '';

    
    private $authorEmail = '';

    
    public function __construct($authorName, $authorEmail)
    {
        Assert::string($authorName);
        Assert::string($authorEmail);
        if ($authorEmail && !filter_var($authorEmail, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('The author tag does not have a valid e-mail address');
        }

        $this->authorName  = $authorName;
        $this->authorEmail = $authorEmail;
    }

    
    public function getAuthorName()
    {
        return $this->authorName;
    }

    
    public function getEmail()
    {
        return $this->authorEmail;
    }

    
    public function __toString()
    {
        return $this->authorName . '<' . $this->authorEmail . '>';
    }

    
    public static function create($body)
    {
        Assert::string($body);

        $splitTagContent = preg_match('/^([^\<]*)(?:\<([^\>]*)\>)?$/u', $body, $matches);
        if (!$splitTagContent) {
            return null;
        }

        $authorName = trim($matches[1]);
        $email = isset($matches[2]) ? trim($matches[2]) : '';

        return new static($authorName, $email);
    }
}
