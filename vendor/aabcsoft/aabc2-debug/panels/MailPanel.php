<?php


namespace aabc\debug\panels;

use Aabc;
use aabc\base\Event;
use aabc\debug\models\search\Mail;
use aabc\debug\Panel;
use aabc\mail\BaseMailer;
use aabc\helpers\FileHelper;
use aabc\mail\MessageInterface;


class MailPanel extends Panel
{
    
    public $mailPath = '@runtime/debug/mail';

    
    private $_messages = [];


    
    public function init()
    {
        parent::init();
        Event::on(BaseMailer::className(), BaseMailer::EVENT_AFTER_SEND, function ($event) {

            /* @var $message MessageInterface */
            $message = $event->message;
            $messageData = [
                    'isSuccessful' => $event->isSuccessful,
                    'from' => $this->convertParams($message->getFrom()),
                    'to' => $this->convertParams($message->getTo()),
                    'reply' => $this->convertParams($message->getReplyTo()),
                    'cc' => $this->convertParams($message->getCc()),
                    'bcc' => $this->convertParams($message->getBcc()),
                    'subject' => $message->getSubject(),
                    'charset' => $message->getCharset(),
            ];

            // add more information when message is a SwiftMailer message
            if ($message instanceof \aabc\swiftmailer\Message) {
                /* @var $swiftMessage \Swift_Message */
                $swiftMessage = $message->getSwiftMessage();

                $body = $swiftMessage->getBody();
                if (empty($body)) {
                    $parts = $swiftMessage->getChildren();
                    foreach ($parts as $part) {
                        if (!($part instanceof \Swift_Mime_Attachment)) {
                            /* @var $part \Swift_Mime_MimePart */
                            if ($part->getContentType() == 'text/plain') {
                                $messageData['charset'] = $part->getCharset();
                                $body = $part->getBody();
                                break;
                            }
                        }
                    }
                }

                $messageData['body'] = $body;
                $messageData['time'] = $swiftMessage->getDate();
                $messageData['headers'] = $swiftMessage->getHeaders();

            }

            // store message as file
            $fileName = $event->sender->generateMessageFileName();
            FileHelper::createDirectory(Aabc::getAlias($this->mailPath));
            file_put_contents(Aabc::getAlias($this->mailPath) . '/' . $fileName, $message->toString());
            $messageData['file'] = $fileName;

            $this->_messages[] = $messageData;
        });
    }

    
    public function getName()
    {
        return 'Mail';
    }

    
    public function getSummary()
    {
        return Aabc::$app->view->render('panels/mail/summary', ['panel' => $this, 'mailCount' => count($this->data)]);
    }

    
    public function getDetail()
    {
        $searchModel = new Mail();
        $dataProvider = $searchModel->search(Aabc::$app->request->get(), $this->data);

        return Aabc::$app->view->render('panels/mail/detail', [
                'panel' => $this,
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel
        ]);
    }

    
    public function save()
    {
        return $this->getMessages();
    }

    
    public function getMessages()
    {
        return $this->_messages;
    }

    private function convertParams($attr)
    {
        if (is_array($attr)) {
            $attr = implode(', ', array_keys($attr));
        }

        return $attr;
    }
}
