<?php

namespace Faker\Provider\fr_FR;

use Faker\Calculator\Luhn;

class Company extends \Faker\Provider\Company
{
    
    protected static $formats = array(
        '{{lastName}} {{companySuffix}}',
        '{{lastName}} {{lastName}} {{companySuffix}}',
        '{{lastName}}',
        '{{lastName}}',
    );

    
    protected static $catchPhraseFormats = array(
        '{{catchPhraseNoun}} {{catchPhraseVerb}} {{catchPhraseAttribute}}',
    );

    
    protected static $noun = array(
        'la sécurité', 'le plaisir', 'le confort', 'la simplicité', "l'assurance", "l'art", 'le pouvoir', 'le droit',
        'la possibilité', "l'avantage", 'la liberté'
    );

    
    protected static $verb = array(
        'de rouler', "d'avancer", "d'évoluer", 'de changer', "d'innover", 'de louer', "d'atteindre vos buts",
        'de concrétiser vos projets'
    );

    
    protected static $attribute = array(
        'de manière efficace', 'plus rapidement', 'plus facilement', 'plus simplement', 'en toute tranquilité',
        'avant-tout', 'autrement', 'naturellement', 'à la pointe', 'sans soucis', "à l'état pur",
        'à sa source', 'de manière sûre', 'en toute sécurité'
    );

    
    protected static $companySuffix = array('SA', 'S.A.', 'SARL', 'S.A.R.L.', 'S.A.S.', 'et Fils');

    protected static $siretNicFormats = array('####', '0###', '00#%');

    
    public function catchPhraseNoun()
    {
        return static::randomElement(static::$noun);
    }

    
    public function catchPhraseAttribute()
    {
        return static::randomElement(static::$attribute);
    }

    
    public function catchPhraseVerb()
    {
        return static::randomElement(static::$verb);
    }

    
    public function catchPhrase()
    {
        do {
            $format = static::randomElement(static::$catchPhraseFormats);
            $catchPhrase = ucfirst($this->generator->parse($format));

            if ($this->isCatchPhraseValid($catchPhrase)) {
                break;
            }
        } while (true);

        return $catchPhrase;
    }

    
    public function siret($formatted = true)
    {
        $siret = $this->siren(false);
        $nicFormat = static::randomElement(static::$siretNicFormats);
        $siret .= $this->numerify($nicFormat);
        $siret .= Luhn::computeCheckDigit($siret);
        if ($formatted) {
            $siret = substr($siret, 0, 3) . ' ' . substr($siret, 3, 3) . ' ' . substr($siret, 6, 3) . ' ' . substr($siret, 9, 5);
        }

        return $siret;
    }

    
    public function siren($formatted = true)
    {
        $siren = $this->numerify('%#######');
        $siren .= Luhn::computeCheckDigit($siren);
        if ($formatted) {
            $siren = substr($siren, 0, 3) . ' ' . substr($siren, 3, 3) . ' ' . substr($siren, 6, 3);
        }

        return $siren;
    }

    
    protected static $wordsWhichShouldNotAppearTwice = array('sécurité', 'simpl');

    
    protected static function isCatchPhraseValid($catchPhrase)
    {
        foreach (static::$wordsWhichShouldNotAppearTwice as $word) {
            // Fastest way to check if a piece of word does not appear twice.
            $beginPos = strpos($catchPhrase, $word);
            $endPos = strrpos($catchPhrase, $word);

            if ($beginPos !== false && $beginPos != $endPos) {
                return false;
            }
        }

        return true;
    }
}
