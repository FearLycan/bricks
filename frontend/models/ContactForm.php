<?php

namespace frontend\models;

use common\models\ContactMessage;
use Throwable;
use Yii;
use yii\base\Model;

/**
 * Contact form. Every message is stored (read in the admin panel, admin/message) and, when an
 * address is configured, also mailed. Spam guards instead of a captcha: a hidden honeypot
 * field, a minimum fill time carried in a signed token, and a per-IP rate limit.
 * Ported from store-base.
 */
class ContactForm extends Model
{
    public string $name = '';
    public string $email = '';
    public string $subject = '';
    public string $body = '';
    /** Honeypot: hidden from people, bots fill it. */
    public string $website = '';
    /** Signed render time. */
    public string $token = '';

    private const MIN_SECONDS = 3;
    private const PER_HOUR = 5;

    public function rules(): array
    {
        return [
            [['name', 'email', 'subject', 'body'], 'trim'],
            [['name', 'email', 'subject', 'body'], 'required'],
            ['name', 'string', 'max' => 120],
            ['email', 'email'],
            ['email', 'string', 'max' => 190],
            ['subject', 'string', 'max' => 200],
            ['body', 'string', 'min' => 10, 'max' => 5000],
            [['website', 'token'], 'safe'],
        ];
    }

    public static function issueToken(): string
    {
        return Yii::$app->security->hashData((string)time(), self::key());
    }

    /**
     * Validates and stores the message. Returns true also for silently dropped spam, so
     * bots get the same "thanks" as people. A mail problem never fails the form.
     */
    public function submit(string $ip): bool
    {
        if (!$this->validate()) {
            return false;
        }
        if ($this->website !== '' || !$this->filledByPerson()) {
            return true;
        }
        $ipHash = hash('sha256', $ip . '|' . self::key());
        $rateKey = ['contact.rate', $ipHash];
        $sent = (int)Yii::$app->cache->get($rateKey);
        if ($sent >= self::PER_HOUR) {
            $this->addError('body', \frontend\components\T::tr('Too many messages from your connection. Please try again in an hour.'));
            return false;
        }
        Yii::$app->cache->set($rateKey, $sent + 1, 3600);

        $m = new ContactMessage();
        $m->setAttributes([
            'name'    => $this->name,
            'email'   => $this->email,
            'subject' => $this->subject,
            'body'    => $this->body,
            'lang'    => mb_substr((string)Yii::$app->language, 0, 5),
            'ip_hash' => $ipHash,
        ], false);
        $m->created_at = time();
        if (!$m->save(false)) {
            $this->addError('body', \frontend\components\T::tr('There was an error sending your message.'));
            return false;
        }
        $this->notify($m);

        return true;
    }

    private function filledByPerson(): bool
    {
        $issued = $this->token !== '' ? Yii::$app->security->validateData($this->token, self::key()) : false;

        return $issued !== false && ctype_digit($issued) && time() - (int)$issued >= self::MIN_SECONDS;
    }

    /** Best effort mail to contact.notifyEmail / alerts.email; failures are only logged. */
    private function notify(ContactMessage $m): void
    {
        $to = self::notifyAddress();
        if ($to === '') {
            return;
        }
        try {
            $mailer = Yii::$app->mailer;
            if (property_exists($mailer, 'useFileTransport') && $mailer->useFileTransport) {
                return;
            }
            $mailer->compose()
                ->setTo($to)
                ->setFrom([(string)Yii::$app->params['senderEmail'] => 'BrickAtlas'])
                ->setReplyTo([$m->email => $m->name])
                // Unique per message: Gmail threads by subject.
                ->setSubject('[BrickAtlas] ' . $m->subject . ' (#' . $m->id . ')')
                ->setTextBody($m->body . "\n\nFrom: " . $m->name . ' <' . $m->email . '>' . "\nLanguage: " . $m->lang)
                ->send();
        } catch (Throwable $e) {
            Yii::warning('Contact notification not sent: ' . $e->getMessage(), __METHOD__);
        }
    }

    /** contact.notifyEmail, else alerts.email; never the no-reply sender or a placeholder. */
    public static function notifyAddress(): string
    {
        $p = Yii::$app->params;
        foreach ([$p['contact.notifyEmail'] ?? '', $p['alerts.email'] ?? ''] as $addr) {
            $addr = trim((string)$addr);
            if ($addr !== '' && !str_ends_with(strtolower($addr), '@example.com') && !str_starts_with(strtolower($addr), 'no-reply@')) {
                return $addr;
            }
        }

        return '';
    }

    private static function key(): string
    {
        return (string)(Yii::$app->request->cookieValidationKey ?? 'contact');
    }
}
