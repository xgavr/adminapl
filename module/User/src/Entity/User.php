<?php
namespace User\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Application\Entity\Contact;
use Company\Entity\Office;

/**
 * This class represents a registered user.
 * @ORM\Entity(repositoryClass="\User\Repository\UserRepository")
 * @ORM\Table(name="user")
 */
class User 
{
    // User status constants.
    const STATUS_ACTIVE       = 1; // Active user.
    const STATUS_RETIRED      = 2; // Retired user.
    
    /**
     * @ORM\Id
     * @ORM\Column(name="id")
     * @ORM\GeneratedValue
     */
    protected $id;

    /** 
     * @ORM\Column(name="apl_id")  
     */
    protected $aplId;
    
    /** 
     * @ORM\Column(name="email")  
     */
    protected $email;
    
    /** 
     * @ORM\Column(name="full_name")  
     */
    protected $fullName;

    /** 
     * @ORM\Column(name="password")  
     */
    protected $password;

    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;
    
    /**
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;
        
    /**
     * @ORM\Column(name="birthday")  
     */
    protected $birthday;
        
    /**
     * @ORM\Column(name="order_count")  
     */
    protected $orderCount;

    /**
     * @ORM\Column(name="balance")   
     */
    protected $balance;
    
    /**
     * @ORM\Column(name="pwd_reset_token")  
     */
    protected $passwordResetToken;
    
    /**
     * @ORM\Column(name="pwd_reset_token_creation_date")  
     */
    protected $passwordResetTokenCreationDate;
    
    /**
     * @ORM\ManyToMany(targetEntity="User\Entity\Role")
     * @ORM\JoinTable(name="user_role",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     *      )
     */
    private $roles;
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Contact", mappedBy="user")
    * @ORM\JoinColumn(name="id", referencedColumnName="user_id")
     */
    private $contacts;
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Client", mappedBy="user")
    * @ORM\JoinColumn(name="id", referencedColumnName="manager_id")
     */
    private $clients;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="users") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    private $office;    
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->roles = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->clients = new ArrayCollection();
    }
    
    /**
     * Returns user ID.
     * @return integer
     */
    public function getId() 
    {
        return $this->id;
    }
    
    public function getLink()
    {
        return "<a href='/users/view/{$this->id}' target='_blank'>{$this->fullName}</a>";                
    }

    /**
     * Sets user ID. 
     * @param int $id    
     */
    public function setId($id) 
    {
        $this->id = $id;
    }

    /**
     * Returns user apl ID.
     * @return integer
     */
    public function getAplId() 
    {
        return $this->aplId;
    }

    /**
     * Sets user apl ID. 
     * @param int $aplId    
     */
    public function setAplId($aplId) 
    {
        $this->aplId = $aplId;
    }

    /**
     * Returns email.     
     * @return string
     */
    public function getEmail() 
    {
        return $this->email;
    }

    /**
     * Sets email.     
     * @param string $email
     */
    public function setEmail($email) 
    {
        $this->email = $email;
    }
    
    /**
     * Returns full name.
     * @return string     
     */
    public function getFullName() 
    {
        return $this->fullName;
    }       

    /**
     * Returns full name.
     * @return string     
     */
    public function getName() 
    {
        return $this->fullName;
    }       

    /**
     * Sets full name.
     * @param string $fullName
     */
    public function setFullName($fullName) 
    {
        $this->fullName = $fullName;
    }
    
    /**
     * Returns status.
     * @return int     
     */
    public function getStatus() 
    {
        return $this->status;
    }

    /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getStatusList() 
    {
        return [
            self::STATUS_ACTIVE => 'Работает',
            self::STATUS_RETIRED => 'Уволен'
        ];
    }    
    
    /**
     * Returns user status as string.
     * @return string
     */
    public function getStatusAsString()
    {
        $list = self::getStatusList();
        if (isset($list[$this->status]))
            return $list[$this->status];
        
        return 'Unknown';
    }    
    
    /**
     * Sets status.
     * @param int $status     
     */
    public function setStatus($status) 
    {
        $this->status = $status;
    }   
    
    /**
     * Returns password.
     * @return string
     */
    public function getPassword() 
    {
       return $this->password; 
    }
    
    /**
     * Sets password.     
     * @param string $password
     */
    public function setPassword($password) 
    {
        $this->password = $password;
    }
    
    /**
     * Returns the date of user creation.
     * @return string     
     */
    public function getDateCreated() 
    {
        return $this->dateCreated;
    }
    
    /**
     * Sets the date when this user was created.
     * @param string $dateCreated     
     */
    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
    }    
    
    /**
     * Returns the date of birth creation.
     * @return string     
     */
    public function getBirthday() 
    {
        return $this->birthday;
    }
    
    /**
     * Sets the date when this user was birth.
     * @param string $dateCreated     
     */
    public function setBirthday($birthday) 
    {
        $this->birthday = ($birthday) ? $birthday:date('Y-m-d');
    }    
    
    /**
     * Returns the order count.
     * @return integer     
     */
    public function getOrderCount() 
    {
        return $this->orderCount;
    }
    
    /**
     * Sets the order count.
     * @param integer $orderCount     
     */
    public function setOrderCount($orderCount) 
    {
        $this->orderCount = $orderCount;
    }    

    /**
     * 
     * @return float
     */
    public function getBalance() {
        return $this->balance;
    }

    /**
     * 
     * @param float $balance
     * @return $this
     */
    public function setBalance($balance) {
        $this->balance = $balance;
        return $this;
    }
    
    /**
     * Returns password reset token.
     * @return string
     */
    public function getResetPasswordToken()
    {
        return $this->passwordResetToken;
    }
    
    /**
     * Sets password reset token.
     * @param string $token
     */
    public function setPasswordResetToken($token) 
    {
        $this->passwordResetToken = $token;
    }
    
    /**
     * Returns password reset token's creation date.
     * @return string
     */
    public function getPasswordResetTokenCreationDate()
    {
        return $this->passwordResetTokenCreationDate;
    }
    
    /**
     * Sets password reset token's creation date.
     * @param string $date
     */
    public function setPasswordResetTokenCreationDate($date) 
    {
        $this->passwordResetTokenCreationDate = $date;
    }
        
    /**
     * Returns the array of roles assigned to this user.
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }
    
    /**
     * Returns the string of assigned role names.
     */
    public function getRolesAsString()
    {
        $roleList = '';
        
        $count = count($this->roles);
        $i = 0;
        foreach ($this->roles as $role) {
            $roleList .= $role->getName();
            if ($i<$count-1)
                $roleList .= ', ';
            $i++;
        }
        
        return $roleList;
    }
    
    public function getRolesAsArray()
    {
        $roleList = [];
        
        foreach ($this->roles as $role) {
            $roleList[] = $role->getId();
        }
        
        return $roleList;
    }

    /**
     * Assigns a role to user.
     */
    public function addRole($role)
    {
        $this->roles->add($role);
    }
    
    /**
     * Returns contact.
     * @return array
     */
    public function getContacts()
    {
        return $this->contacts;
    }
    
    /**
     * Получить телефоны сотрудника
     */
    public function getPhones()
    {
        $result = [];
        foreach ($this->contacts as $contact){
            $result[] = $contact->getPhonesAsString();
        }
        
        return implode(', ', array_filter($result));
    }
        
    /**
     * Assigns.
     */
    public function addContact($contact)
    {
        $this->contacts[] = $contact;
    }
        
    /**
     * Returns the array of for legal contacts assigned to this.
     * @return array
     */
    public function getLegalContacts()
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq("status", Contact::STATUS_LEGAL));
        return $this->getContacts()->matching($criteria);
    }
        
    /**
     * Returns the array of for first legal contact assigned to this.
     * @return array
     */
    public function getLegalContact()
    {
        $contacts = $this->getLegalContacts();
        return $contacts[0];
    }
        
    /**
     * Подпись в письме
     * @return string
     */
    public function getSign()
    {
        $legalContact = $this->getLegalContact();
        if ($legalContact){
            return $legalContact->getSignature();
        }
        
        return;
    }
        
    /**
     * Returns the array of for other contacts assigned to this.
     * @return array
     */
    public function getOtherContacts()
    {
        $criteria = Criteria::create()->where(Criteria::expr()->neq("status", Contact::STATUS_LEGAL));
        return $this->getContacts()->matching($criteria);
    }
        
    /**
     * Returns clients.
     * @return array
     */
    public function getClients()
    {
        return $this->clients;
    }
        
    /**
     * Assigns.
     */
    public function addClient($client)
    {
        $this->clients[] = $client;
    }
        
    /**
     * Returns the office.
     * @return Office     
     */
    public function getOffice() 
    {
        return $this->office;
    }
    
    /**
     * Sets  office.
     * @param Office $office     
     */
    public function setOffice($office) 
    {
        $this->office = $office;
        if ($office){
            $office->addUser($this);
        }    
    }      
    
    public function toArray()
    {
        return [
            'aplId' => $this->getAplId(),
            'birthday' => $this->getBirthday(),
            'dateCreated' => $this->getDateCreated(),
            'email' => $this->getEmail(),
            'fullName' => $this->getFullName(),
            'id' => $this->getId(),
            'link' => $this->getLink(),
            'name' => $this->getName(),
            'office' => $this->getOffice()->getId(),
            'officeName' => $this->getOffice()->getName(),
            'orderCount' => $this->getOrderCount(),
            'phones' => $this->getPhones(),
            'roles' => $this->getRolesAsString(),
            'status' => $this->getStatus(),
            'balance' => $this->getBalance(),
            'statusAsString' => $this->getStatusAsString(),
        ];
    }
}



