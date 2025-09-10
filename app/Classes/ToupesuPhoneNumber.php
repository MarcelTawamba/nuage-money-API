<?php


namespace App\Classes;

use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberToCarrierMapper;

class ToupesuPhoneNumber
{
    private $phoneNumber;
    private $isValidNumber;
    private $isMobileNumber;
    private $regionCode;
    private $countryCode;
    private $nationalNumber;
    private $internationNumber;
    public  $carrier;

    /**
     * ToupesuPhoneNumber constructor.
     * @param $phoneNumber
     * @throws \libphonenumber\NumberParseException
     */
    function __construct($phoneNumber)
    {
        $this->phoneNumber = $this->RemoveSpaceAndDash($phoneNumber);
        $this->isValidNumber = false;
        $this->isMobileNumber = false;
        $this->regionCode = null;
        $this->nationalNumber = null;
        $this->internationNumber = null;
        $this->countryCode = null;
        $this->carrier=null;
        $this->ValidatedNumber();


    }

    /**
     * @throws \libphonenumber\NumberParseException
     */
    private function ValidatedNumber() {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $parseNumber = $phoneUtil->parse($this->phoneNumber, "CM");
        if(!$phoneUtil->isValidNumber($parseNumber)) {
            $this->isValidNumber = false;
        } else if (($phoneUtil->getNumberType($parseNumber) != PhoneNumberType::MOBILE) && ($phoneUtil->getNumberType($parseNumber) != PhoneNumberType::FIXED_LINE_OR_MOBILE)) {
            $this->isValidNumber = true;
            $this->isMobileNumber = false;
        } else {
            $this->isMobileNumber = true;
            $this->isValidNumber = true;
            $this->regionCode = $phoneUtil->getRegionCodeForNumber($parseNumber);
            $this->countryCode = $parseNumber->getCountryCode();
            $this->nationalNumber = str_replace(" ", "", $phoneUtil->format($parseNumber, PhoneNumberFormat::NATIONAL));
            $this->internationNumber = str_replace(" ", "", $phoneUtil->format($parseNumber, PhoneNumberFormat::INTERNATIONAL));
            $carrierMapper = PhoneNumberToCarrierMapper::getInstance();
            $this->carrier = $carrierMapper->getNameForNumber($parseNumber, 'en');
        }
    }

    /**
     * @return bool
     */
    public function IsValidNumber() {
        return $this->isValidNumber;
    }

    /**
     * @return bool
     */
    public function IsMobileNumber() {
        return $this->isMobileNumber;
    }

    /**
     * @return string
     */
    public function GetRegionCode() {
        return $this->regionCode;
    }

    /**
     * @return int
     */
    public function GetCountryCode() {
        return $this->countryCode;
    }

    /**
     * @return string
     */
    public function GetNationalNumber() {
        return $this->RemoveSpaceAndDash($this->nationalNumber);
    }

    /**
     * @return string
     */
    public function GetInternationalNumber() {

        return $this->RemoveSpaceAndDash($this->internationNumber);
    }

    /**
     * @param $message
     * @return bool|mixed|string
     */
    public function SendSMS($message) {
        if(!$this->isValidNumber || !$this->isMobileNumber)
            return false;

        $url = env("SMS_MAIN_URL")."/sms/other";        //live

        $data = array(
            'phonenumber' => $this->internationNumber,
            'messagetext' => $message,
        );




        $resultat = curlPostData($url, $data);
        $resultat = $resultat["resultat"];
        $resultat = json_decode($resultat,TRUE);
        $data["url"] = $url;

        return $resultat;
    }

    private function RemoveSpaceAndDash($phoneNumber) {
        $phoneNumber = str_replace(" ", "", $phoneNumber);
        $phoneNumber = str_replace("-", "", $phoneNumber);
        $phoneNumber = str_replace("(", "", $phoneNumber);
        $phoneNumber = str_replace(")", "", $phoneNumber);
        return $phoneNumber;
    }






}
