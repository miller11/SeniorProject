<?php
/**
 * User: Ross
 * Date: 11/24/12
 */
class App
{

    private $firstName;
    private $lastName;
    private $website;
    private $phdYear;
    private $phdMonth;
    private $cvDate;
    private $coverLetterDate;
    private $researchStatementDate;
    private $reference1;
    private $reference2;
    private $reference3;
    private $gender;


    function __construct($result)
    {
        $this->firstName = $result['FIRST_NAME'];
        $this->lastName = $result['LAST_NAME'];
        $this->website = $result['WEBSITE'];
        $datePieces = explode("-", $result['PHD_DATE']);
        $this->phdYear = $datePieces[0];
        $this->phdMonth = $datePieces[1];

        if($result['COVER_LETTER'] != null) {
        $this->coverLetterDate = $result['UPLOAD_DATE'];
        }
        if($result['CV'] != null){
        $this->cvDate = $result['UPLOAD_DATE'];
        }
        if($result['RESEARCH_STATEMENT']){
            $this->researchStatementDate = $result['UPLOAD_DATE'];
        }

        $results = queryDB("select NUP.EMAIL from NON_UI_PERSON NUP
                    left outer join LETTER_OF_REC LOR on LOR.NON_UI_PERSON_ID = NUP.ID
                    where LOR.APPLICATION_APP_ID = '" . $result['APP_ID'] . "'");
        if($row = nextRow($results)){
            $this->reference1 = $row['EMAIL'];
        }
        if($row = nextRow($results)){
            $this->reference2 = $row['EMAIL'];
        }
        if($row = nextRow($results)){
            $this->reference3 = $row['EMAIL'];
        }
    }

    public function setFirstName($FirstName)
    {
        $this->firstName = $FirstName;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setCoverLetterDate($coverLetterDate)
    {
        $this->coverLetterDate = $coverLetterDate;
    }

    public function getCoverLetterDate()
    {
        return $this->coverLetterDate;
    }

    public function setCvDate($cvDate)
    {
        $this->cvDate = $cvDate;
    }

    public function getCvDate()
    {
        return $this->cvDate;
    }

    public function setResearchStatementDate($researchStatementDate)
    {
        $this->researchStatementDate = $researchStatementDate;
    }

    public function getResearchStatementDate()
    {
        return $this->researchStatementDate;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function setPhdMonth($phdMonth)
    {
        $this->phdMonth = $phdMonth;
    }

    public function getPhdMonth()
    {
        return $this->phdMonth;
    }

    public function setPhdYear($phdYear)
    {
        $this->phdYear = $phdYear;
    }

    public function getPhdYear()
    {
        return $this->phdYear;
    }

    public function setReference1($reference1)
    {
        $this->reference1 = $reference1;
    }

    public function getReference1()
    {
        return $this->reference1;
    }

    public function setReference2($reference2)
    {
        $this->reference2 = $reference2;
    }

    public function getReference2()
    {
        return $this->reference2;
    }

    public function setReference3($reference3)
    {
        $this->reference3 = $reference3;
    }

    public function getReference3()
    {
        return $this->reference3;
    }

    public function setWebsite($website)
    {
        $this->website = $website;
    }

    public function getWebsite()
    {
        return $this->website;
    }

    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    public function getGender()
    {
        return $this->gender;
    }
}