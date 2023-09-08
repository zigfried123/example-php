<?php

namespace app\modules\support\models\status;


use InvalidArgumentException;

class Type
{
    const DRAFT = 0;
    const NEW = 1;
    const IN_PROGRESS = 2;
    const COMPLETED = 3;
    const ON_HOLD = 4;
    const DECLINED = 5;
    const EXECUTED = 6;
    const CANCELED = 7;
    const IN_QUEUE = 101;
    const WORK_INITIATOR = 102;
    const HEAD_APPROVED = 103;
    const ADMINISTRATION_APPROVAL = 104;
    const PAYMENT_ACCOUNTING = 105;
    const PAYMENT_ADMINISTRATION = 106;
    const AGREED = 107;
    const REVIEW = 108;
    const IN_TEST = 109;
    const IN_ASSEMBLY = 110;
    const AGREED_LEADER = 118;
    const ANALYSIS = 119;
    const PREPARE_DOCUMENTS = 121;
    const NEED_CONSIDER_RESUME = 122;
    const DEMO = 126;
    const IN_FEEDBACK = 127;
    const JOB_INTERVIEW = 128;
    const PENDING = 129;
    const APPLICANT_PENDING = 130;
    const TEST_PROD = 132;
    const TESTED = 134;
    const INTEGRATION = 135;
    const OFFER_START = 136;
    const OFFER_OPTIMIZE = 137;
    const PREPARE_CONTRACT = 138;
    const CONTRACT_SIGN = 140;
    const SEND_SBIS = 141;
    const UPLOAD_TO_SBIS = 142;
    const CLOSE_IN_SBIS = 144;
    const INVOICING = 145;
    const MANUAL_PROCESS = 146;
    const COORDINATION_WITH_HEAD = 147;
    const COORDINATION_WITH_GENERAL = 148;
    const ACCOUNTING = 149;
    const NOT_AGREED = 150;
    const REVIEW_COMPLETED = 151;
    const BACKLOG = 152;
    const DEMO_AND_FINISH = 153;
    const PROTOTYPING = 154;
    const HAVE_FEEDBACK = 158;
    const REQUIREMENTS_COLLECTION = 159;
    const CONFIRMED = 160;
    const NEED_MONEY = 165;
    const NEED_AGREED = 166;

    const TYPES = [
        self::DRAFT,
        self::NEW,
        self::IN_PROGRESS,
        self::COMPLETED,
        self::ON_HOLD,
        self::DECLINED,
        self::EXECUTED,
        self::CANCELED,
        self::IN_QUEUE,
        self::WORK_INITIATOR,
        self::HEAD_APPROVED,
        self::ADMINISTRATION_APPROVAL,
        self::PAYMENT_ACCOUNTING,
        self::PAYMENT_ADMINISTRATION,
        self::AGREED,
        self::REVIEW,
        self::IN_TEST,
        self::IN_ASSEMBLY,
        self::AGREED_LEADER,
        self::ANALYSIS,
        self::PREPARE_DOCUMENTS,
        self::NEED_CONSIDER_RESUME,
        self::DEMO,
        self::IN_FEEDBACK,
        self::JOB_INTERVIEW,
        self::PENDING,
        self::APPLICANT_PENDING,
        self::TEST_PROD,
        self::TESTED,
        self::INTEGRATION,
        self::OFFER_START,
        self::OFFER_OPTIMIZE,
        self::PREPARE_CONTRACT,
        self::CONTRACT_SIGN,
        self::SEND_SBIS,
        self::UPLOAD_TO_SBIS,
        self::CLOSE_IN_SBIS,
        self::INVOICING,
        self::MANUAL_PROCESS,
        self::COORDINATION_WITH_HEAD,
        self::COORDINATION_WITH_GENERAL,
        self::ACCOUNTING,
        self::NOT_AGREED,
        self::REVIEW_COMPLETED,
        self::BACKLOG,
        self::DEMO_AND_FINISH,
        self::PROTOTYPING,
        self::HAVE_FEEDBACK,
        self::REQUIREMENTS_COLLECTION,
        self::CONFIRMED,
        self::NEED_MONEY,
        self::NEED_AGREED,
    ];

    const TITLES = [
        self::DRAFT => 'Черновик',
        self::NEW => 'Новая',
        self::IN_PROGRESS => 'В работе',
        self::COMPLETED => 'Завершенная',
        self::ON_HOLD => 'Отложенная',
        self::DECLINED => 'Отклоненная',
        self::EXECUTED => 'Выполненная',
        self::CANCELED => 'Отмененная',
        self::IN_QUEUE => 'В очереди',
        self::WORK_INITIATOR => 'Доработка инициатором',
        self::HEAD_APPROVED => 'Согласование у руководителя',
        self::ADMINISTRATION_APPROVAL => 'Согласование у администрации',
        self::PAYMENT_ACCOUNTING => 'Оплата через бухгалтерию',
        self::PAYMENT_ADMINISTRATION => 'Оплата через администрацию',
        self::AGREED => 'Согласовано',
        self::REVIEW => 'В работе. Review',
        self::IN_TEST => 'В работе. В тестировании',
        self::IN_ASSEMBLY => 'В работе. В сборке',
        self::AGREED_LEADER => 'Согласовано руководителем',
        self::ANALYSIS => 'В работе. Анализ',
        self::PREPARE_DOCUMENTS => 'Подготовить документы',
        self::NEED_CONSIDER_RESUME => 'Необходимо рассмотреть резюме',
        self::DEMO => 'Демонстрация',
        self::IN_FEEDBACK => 'В обратной связи',
        self::JOB_INTERVIEW => 'Собеседование',
        self::PENDING => 'В ожидании',
        self::APPLICANT_PENDING => 'Кандидат на рассмотрении',
        self::TEST_PROD => 'В тестировании на prod',
        self::TESTED => 'В работе. Протестировано',
        self::INTEGRATION => 'Интеграция',
        self::OFFER_START => 'Запуск оффера',
        self::OFFER_OPTIMIZE => 'Оптимизация оффера',
        self::PREPARE_CONTRACT => 'Подготовка договора',
        self::CONTRACT_SIGN => 'Договор подписан',
        self::SEND_SBIS => 'На отправку в СБИС',
        self::UPLOAD_TO_SBIS => 'Загрузка в СБИС',
        self::CLOSE_IN_SBIS => 'Закрытие документов в СБИС',
        self::INVOICING => 'Выставление счета',
        self::MANUAL_PROCESS => 'Ручная обработка',
        self::COORDINATION_WITH_HEAD => 'Согласование с руководителем',
        self::COORDINATION_WITH_GENERAL => 'Согласование с генеральным директором',
        self::ACCOUNTING => 'Оформление в бухгалтерии',
        self::NOT_AGREED => 'Не согласовано',
        self::REVIEW_COMPLETED => 'Review завершено',
        self::BACKLOG => 'Беклог',
        self::DEMO_AND_FINISH => 'Демонстрация, сдача, доработка',
        self::PROTOTYPING => 'Прототипирование',
        self::HAVE_FEEDBACK => 'Есть обратная связь',
        self::REQUIREMENTS_COLLECTION => 'Сбор требований',
        self::CONFIRMED => 'Подтвержден',
        self::NEED_MONEY => 'Требуется оплата',
        self::NEED_AGREED => 'Требуется согласование',
    ];

    /** @var int */
    private $code;

    public function __construct(int $code)
    {
        if (!in_array($code, self::TYPES)) {
            throw new InvalidArgumentException(sprintf('Type with code %s not exist', $code));
        }

        $this->code = $code;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getTitle(): string
    {
        return array_key_exists($this->code, self::TITLES) ? self::TITLES[$this->code] : '';
    }

    public function isCompleted(): bool
    {
        return $this->code === self::COMPLETED;
    }
}