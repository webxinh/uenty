<?php

namespace Faker\Provider\ko_KR;

class Internet extends \Faker\Provider\Internet
{
    protected static $userNameFormats = array(
        '{{lastNameAscii}}.{{firstNameAscii}}', '{{firstNameAscii}}.{{lastNameAscii}}', '{{firstNameAscii}}##', '?{{lastNameAscii}}',
    );

    protected static $safeEmailTld = array(
        'com', 'kr', 'me', 'net', 'org',
    );

    protected static $tld = array(
        'biz', 'com', 'info', 'kr', 'net', 'org',
    );

    
    protected static $lastNameAscii = array(
        'ahn', 'bae', 'baek', 'chang', 'cheon', 'cho', 'choi', 'chung', 'gang', 'go', 'gwak', 'gwon', 'ha', 'han',
        'heo', 'hong', 'hwang', 'jang', 'jeon', 'jo', 'jung', 'kang', 'kim', 'ko', 'kwak', 'kwon', 'lee', 'lim', 'moon',
        'nam', 'no', 'oh', 'park', 'ryu', 'seo', 'shim', 'shin', 'son', 'song', 'yang', 'yoon', 'yu',
    );

    
    protected static $firstNameAscii = array(
        'areum', 'arin', 'banhee', 'bom', 'bomi', 'bomin', 'boram', 'byungcheol', 'byungho', 'chaehyun', 'chaewon',
        'changyoung', 'daesoo', 'daesun', 'dayoung', 'dohyunn', 'dongha', 'donghyun', 'donghyun', 'dongyoon', 'doyoon',
        'doyoun', 'eunae', 'eunhee', 'eunhye', 'eunhyoung', 'eunji', 'eunjin', 'eunju', 'eunjung', 'eunkyoung', 'eunmi',
        'eunsang', 'eunseo', 'eunsung', 'eunteck', 'eunyoung', 'gangeun', 'ganghee', 'garam', 'geongeun', 'gunho',
        'gunwoo', 'haeun', 'hana', 'hanna', 'hayun', 'heekyoung', 'heewon', 'hojin', 'homin', 'hongsun', 'hyejin',
        'hyemin', 'hyena', 'hyerim', 'hyesuk', 'hyesun', 'hyeyoun', 'hyoil', 'hyojin', 'hyounjung', 'hyuksang',
        'hyungcheol', 'hyungmin', 'hyunji', 'hyunjong', 'hyunjoo', 'hyunjun', 'hyunkyu', 'hyunwoo', 'hyunyoung',
        'ingyu', 'inhwa', 'jaecheo', 'jaeho', 'jaehun', 'jaehyuk', 'jaehyun', 'jaeyeon', 'jaeyun', 'jia', 'jieun',
        'jihee', 'jihoo', 'jihoon', 'jihye', 'jihyeon', 'jimin', 'jina', 'jinhee', 'jinho', 'jinsoo', 'jinwoo', 'jisuk',
        'jisun', 'jiwon', 'jiwoo', 'jiye', 'jiyeon', 'jiyoung', 'jonghun', 'jongju', 'jongsoo', 'jughyung', 'juhee',
        'jumi', 'jumyoung', 'jun', 'junbum', 'jungeun', 'jungho', 'junghun', 'junghwa', 'jungmin', 'jungnam', 'jungran',
        'jungshik', 'jungsoo', 'jungsoo', 'jungwoong', 'junho', 'junhyuk', 'junhyung', 'junseo', 'junyoung', 'juwon',
        'juyeon', 'kisoo', 'kiyun', 'kubum', 'kwangsoo', 'kyungchoon', 'kyunghwan', 'kyungjoo', 'kyungseok', 'kyungsoo',
        'kyusan', 'mijung', 'mikyoung', 'mina', 'mincheol', 'minhee', 'minhwan', 'minhyoung', 'minjae', 'minji',
        'minjun', 'minseo', 'minseok', 'minsoo', 'minsung', 'mira', 'miran', 'miyoung', 'moonchang', 'moonyong',
        'myungho', 'myungshik', 'naeun', 'nahyoung', 'namho', 'namsoo', 'naree', 'naroo', 'nayun', 'nuree', 'saemi',
        'sangah', 'sangcheol', 'sangho', 'sanghun', 'sanghyun', 'sangjun', 'sangmyoung', 'sangsoo', 'sangsun',
        'sangwoo', 'sangwook', 'seoho', 'seohyeon', 'seojun', 'seoyeon', 'seoyoung', 'seoyun', 'seulki', 'seungho',
        'seunghyun', 'seungmin', 'sewon', 'sieun', 'sinae', 'siwoo', 'sojung', 'somin', 'soyoun', 'soyoung', 'subin',
        'sujin', 'sujung', 'sumin', 'sungeun', 'sunggon', 'sungho', 'sunghun', 'sunghyun', 'sungjin', 'sungmi',
        'sungmin', 'sungmin', 'sungryung', 'sungsoo', 'sunhang', 'sunho', 'sunjung', 'sunwoo', 'sunyoung', 'sunyup',
        'suran', 'suwon', 'suwon', 'suyoun', 'taehee', 'taeho', 'taehyun', 'wonhee', 'wonjin', 'wonjun', 'woojin',
        'yeji', 'yejin', 'yejun', 'yeojin', 'yeon', 'yewon', 'youngcheol', 'younggil', 'youngha', 'youngho', 'younghun',
        'younghwa', 'youngil', 'youngjin', 'youngjin', 'youngshik', 'youngsoo', 'youngtae', 'youngwhan', 'youngwhan',
        'younhee', 'younsun', 'yujin', 'yujung', 'yunkyoung', 'yunmi', 'yunseo', 'yunyoung', 'yuri'
    );

    public static function lastNameAscii()
    {
        return static::randomElement(static::$lastNameAscii);
    }

    public static function firstNameAscii()
    {
        return static::randomElement(static::$firstNameAscii);
    }

    
    public function userName()
    {
        $format = static::randomElement(static::$userNameFormats);

        return static::bothify($this->generator->parse($format));
    }

    
    public function domainName()
    {
        return static::randomElement(static::$lastNameAscii) . '.' . $this->tld();
    }
}
