<?php


namespace Custom\Rest;


class LangHelper
{
    public const RU = 'RUSSIAN';
    public const ZH = 'CHINESE';
    public const EN = 'ENGLISH';

    /**
     * @param $lang
     * @return array|string[]
     */
    public static function getTextForStart($lang): array
    {
        switch ($lang) {
            case 'ru':
                return [
                    'SELECT_TEXT' => 'Пожалуйста, выберите страну',
                    'BREADCRUMB' => 'Выбор формы',
                ];
                break;
            case 'zh':
                return [
                    'SELECT_TEXT' => '请选择国家',
                    'BREADCRUMB' => '形式选择',
                ];
                break;
            default:
                return [
                    'SELECT_TEXT' => 'Please select country',
                    'BREADCRUMB' => 'Form',
                ];
        }
    }

    /**
     * @param int $countryId
     * @param string $lang
     * @return array
     */
    public static function getLangDataForFormsList(int $countryId, string $lang): array
    {
        $langButton = self::EN;
        $iblockSectionId = 1;
        if ($countryId === Forms::RUSSIAN_FEDERATION_ID) {
            $iblockSectionId = 2;
            if ($lang === 'ru') {
                $langButton = self::RU;
            }
        }
        if ($countryId === Forms::CHINA_ID) {
            $iblockSectionId = 3;
            if ($lang === 'zh') {
                $langButton = self::ZH;
            }
        }
        return [
            'LANG' => $langButton,
            'IBLOCK_SECTION_ID' => $iblockSectionId];
    }

    /**
     * @param $langForm
     * @return array|string[]
     */
    public static function getTextForSubmit($langForm): array
    {
        switch ($langForm) {
            case self::RU:
                return [
                    'MESSAGE' => 'Спасибо! Ваша Заявка Успешно Отправлена.',
                    'ERROR' => 'При отправке заявки произошла ошибка, попробуйте отправить снова.',
                ];
                break;
            case self::ZH:
                return [
                    'MESSAGE' => '谢谢！您的请求已成功发送。',
                    'ERROR' => '发送请求时发生错误，请尝试重新发送。',
                ];
                break;
            default:
                return [
                    'MESSAGE' => 'Thanks! Your request was successfully submitted.',
                    'ERROR' => 'An error occurred when sending the request, try to send again.',
                ];
        }
    }

    /**
     * @param $lang
     * @param $countryId
     * @return string[]
     */
    public static function getLanguageDataForForm($lang, $countryId): array
    {
        $buttonText = 'Send request';
        $langForm = self::EN;
        if ($lang === 'ru' && $countryId === Forms::RUSSIAN_FEDERATION_ID) {
            $langForm = self::RU;
            $buttonText = 'Отправить';
        }
        if ($lang === 'zh' && $countryId === Forms::CHINA_ID) {
            $langForm = self::ZH;
            $buttonText = '发送请求';
        }
        return [
            'LANG_FORM' => $langForm,
            'BUTTON_TEXT' => $buttonText,
            'ERROR_TEXT' => self::getErrorMessageArray($langForm)
        ];
    }

    /**
     * @param $langForm
     * @return array|string[]
     */
    public static function getErrorMessageArray($langForm): array
    {
        $arErrorMessage = [
            'ENGLISH' => [
                'emptyField' => 'This field should not be empty',
                'required' => 'This field is required',
                'mask' => 'mask is not correct',
                'minNumber' => 'Above value',
                'maxNumber' => 'Below minimum',
                'rangeNumber' => 'Out of range value',
                'maxLength' => 'Maximum length exceeded',
                'valueLang' => 'Invalid input language',
                'fileSize' => 'File size exceeded',
                'captcha' => 'wrong captcha',
                'fileType' => 'file type error'
            ],
            'RUSSIAN' => [
                'emptyField' => 'Это поле не должно быть пустым',
                'required' => 'Это поле обязательно к заполнению',
                'mask' => 'Не соответствует маске',
                'minNumber' => 'Ниже минимального значения',
                'maxNumber' => 'Выше максимального значения',
                'rangeNumber' => 'Вне допустимого значения',
                'maxLength' => 'Максимальная длинна превышена',
                'valueLang' => 'Неверный язык ввода',
                'fileSize' => 'Превышен допустимый размер файла',
                'captcha' => 'Неверно введены символы с картинки',
                'fileType' => 'Неверный тип файла'
            ],
            'CHINESE' => [
                'emptyField' => 'This field should not be empty',
                'required' => 'This field is required',
                'mask' => 'mask is not correct',
                'minNumber' => 'Above value',
                'maxNumber' => 'Below minimum',
                'rangeNumber' => 'Out of range value',
                'maxLength' => 'Maximum length exceeded',
                'valueLang' => 'Invalid input language',
                'fileSize' => 'File size exceeded',
                'captcha' => 'wrong captcha',
                'fileType' => 'file type error'
            ]
        ];
        return $arErrorMessage[$langForm];
    }

    /**
     * @param string $langForm
     * @return array
     */
    public static function getMonthsList(string $langForm): array
    {
        $arMonth = [
            'RUSSIAN' => [
                'Январь' => 'Январь',
                'Февраль' => 'Февраль',
                'Март' => 'Март',
                'Апрель' => 'Апрель',
                'Май' => 'Май',
                'Июнь' => 'Июнь',
                'Июль' => 'Июль',
                'Август' => 'Август',
                'Сентябрь' => 'Сентябрь',
                'Октябрь' => 'Октябрь',
                'Ноябрь' => 'Ноябрь',
                'Декабрь' => 'Декабрь'
            ],
            'ENGLISH' => [
                'January' => 'January',
                'February' => 'February',
                'March' => 'March',
                'April' => 'April',
                'May' => 'May',
                'June' => 'June',
                'July' => 'July',
                'August' => 'August',
                'September' => 'September',
                'October' => 'October',
                'November' => 'November',
                'December' => 'December'
            ],
            'CHINESE' => [
                '一月' => '一月',
                '二月' => '二月',
                '三月' => '三月',
                '四月' => '四月',
                '五月' => '五月',
                '六月' => '六月',
                '七月' => '七月',
                '八月' => '八月',
                '九月' => '九月',
                '十月' => '十月',
                '十一月' => '十一月',
                '十二月' => '十二月'
            ],
        ];

        return $arMonth[$langForm];
    }

    /**
     * @param string $langForm
     * @return string
     */
    public static function getCaptchaLabel(string $langForm): string
    {
        switch ($langForm) {
            case self::RU:
                return 'Введите код с картинки';
                break;
            case self::ZH:
                return '输入图片中的验证码';
                break;
            default:
                return 'Enter the code from the image';
        }
    }
}