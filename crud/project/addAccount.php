<?php

session_status() !== PHP_SESSION_ACTIVE ? session_start() : print (session_status()); // Verify if session has started or not

// ini_get("display_errors") == 1 ? ini_set("display_errors", 0) : "";

error_reporting(E_ERROR | E_PARSE);

include "dataBase.php";


$userName = $_POST['username'];
$passWord = $_POST['password'];

$submitBtn = $_POST['submit'];

$getErrors = NULL;

class addAccount 
{
    // new user id
    private $id;

    // new user name
    private $name;

    // new user authentication
    private $auth;

    // update errors
    public $errors = [];

    public function __construct()
    {
        $this->id = NULL;
        $this->name = NULL;
        $this->auth = NULL;
    }

    public function setErrors($errors)
    {
        $this->errors = $errors;
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }


    
    public function checkNameError(string $name): bool
    {
        $validName = TRUE;

        $nameLen = mb_strlen($name);

        if (empty($name))
        {
            $validName = FALSE;
        }

        if (($nameLen < 0) || ($nameLen > 10))
        {
            $validName = FALSE;
        }

        return $validName;

    }
    
    public function checkPasswordError(string $password): bool
    {
        $validPasswd = TRUE;

        $passWordLen = mb_strlen($password);

        if (empty($password))
        {
            $validPasswd = FALSe;
        }

        if ($passWordLen > 5)
        {
            $validPasswd = FALSE;
        }

        return $validPasswd;
    }
    public function checkIfNameExists(string $name) : bool
    {
        global $database;


        $validNameExists = TRUE;

        $getName = htmlentities(stripslashes(trim($name)));

         $checkIfNameExistQuery = "SELECT crud.accounts.account_name FROM crud.accounts WHERE crud.accounts.account_name=?";

         $checkPrepareNameQuery = $database->prepare($checkIfNameExistQuery);

         $checkPrepareNameQuery->bind_param('s', $getName);
         
         $checkPrepareNameQuery->execute();

         $findPrepareNameFromQuery = $checkPrepareNameQuery->get_result();


         if ($findPrepareNameFromQuery->num_rows == 1)
         {
            $validNameExists = FALSE;
         }

         return $validNameExists;
    }
    public function getIDFromName(string $name): int
    {

        global $database;

        $getName = stripslashes(trim($name));

        $ID = NULL;

        if ($this->checkNameError($name) == FALSE):
            print("Cannot review ID. The name must be empty.");
        endif;

        $findIdFromNameQuery = "SELECT crud.account_id FROM crud.accounts WHERE `account_name`=?";

        $findIdFromNamePrepareQuery = $database->prepare($findIdFromNameQuery);
        $findIdFromNamePrepareQuery->bind_param('s',$getName);
        
        $getRowID = $findIdFromNamePrepareQuery->get_result();

        if ($getRowID->num_rows == 1)
        {
            $rowID = $getRowID->fetch_assoc();

            if (is_array($rowID))
            {
                $ID = $rowID['id'];
            }
        }

        return $ID;
    }

    public function displayErrors(string $name, string $password): array
    {

        $getErrors = $this->getErrors();

        //$idFromName = $this->getIDFromName($name);

        if ($this->checkNameError($name) == FALSE):

            array_push($getErrors, "The name field is either empty or exceeds maximum/minimum character length.");

        endif;

        if ($this->checkPasswordError($password) == FALSE):

            array_push($getErrors, "The password field is either empty or exceeds maximum/minimum character length.");

        endif;

        if ($this->checkIfNameExists($name) == FALSE):

            array_push($getErrors, "Username already exists");

        endif;


        return $getErrors;


    }
    public function _addAccount(string $name, string $password) : int
    {
        global $database;

        $getName = htmlentities(stripslashes(trim($name)));

        $getPassword = htmlentities(stripslashes(trim($password)));

        $rowIDResult = NULL;

        if (count($this->displayErrors($name, $password)) == 0)
        {
            $insertAccountQuery = "INSERT INTO crud.accounts (`account_name`,`account_password`) VALUES (?,?)";

            $hashedPasswordForInsertedQuery = password_hash($getPassword, PASSWORD_DEFAULT);

            $insertPreparedAccountQuery = $database->prepare($insertAccountQuery);

            $insertPreparedAccountQuery->bind_param('ss', $getName, $hashedPasswordForInsertedQuery);

            $insertPreparedAccountQuery->execute();

            $rowIDFromInsertedQuery = $insertPreparedAccountQuery->insert_id;

            $rowIDResult = $rowIDFromInsertedQuery;
        }

        return $rowIDResult;
    
    }


}

function returnAddAccountMethod()
{
    global $userName, $passWord, $submitBtn, $getErrors;

    if (isset($submitBtn))
    {
        $addAccount = new addAccount();
        
        // Check if errors exist

        $getErrors = $addAccount->displayErrors($userName, $passWord);

        if (count($getErrors) == 0) {
        
            //  $newUserID = $addAccount->_addAccount($userName, $passWord);

            //  echo "The New User ID: " . $newUserID;

            $addAccount->_addAccount($userName, $passWord);
        } else
        {
            trigger_error("An Error Has Occurred");
        }

       
    } else
    {
        // throw new Exception("Submit Error ");
        $getAddAccountError = new Exception();

        echo $getAddAccountError->getMessage();
    }

    return $getErrors;
}

// function returnErrorMethod()
// {
//     global $userName, $passWord, $submitBtn;

//     $getErrors = NULL;

//     if (isset($submitBtn))
//     {
//         $displayErrorsFromAccount = new addAccount();

//         $getErrors = $displayErrorsFromAccount->displayErrors($userName, $passWord);
//     }
//     return $getErrors;
// }

returnAddAccountMethod();



?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Add Account</title>
        <style>
             @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700;800;900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
             *
             {
                font-family: 'Cinzel', sans-serif;
             }
             body {
                padding: 100px 700px;
             }

            h1 {
                text-transform: uppercase;
                letter-spacing: 5px;
                width: 400px;
                text-align: center;
                position: relative;
                right: 20px;
            }

            input {
                padding: 20px;
                width: 300px;
            }

            .errors {
                color: red;
                width: 500px;
                text-align: center;
                position: relative;
                right: 80px;
            }

            .showPassword
            {
                position: relative;
                bottom: calc(40px + 2px);
                left: calc(100px + 120px);
                font-size: 12px;
                cursor: pointer;
            }

            input[type='submit'] {
                background-color: black;
                color: white;
                position: relative;
                left: 25px;
                cursor: pointer;
            }

            .options
            {
                margin: 10px;
                display: flex;
                width: 500px;
                justify-content: space-around;
                position: relative;
                right: 80px;
            }
            .options-wrapper
            {
                display: flex;
                position: relative;
                margin: auto;
                width: 400px;
                justify-content: space-evenly;
            }
            .options-wrapper button
            {
                background-color: transparent;
                border: 1px solid black;
                padding: 5px;
                border-radius: 5px;
                cursor: pointer;
                transition: all 250ms ease;
            }
            
            .options-wrapper button a
            {
                text-decoration: none;
                color: black;
            }
        </style>
    </head>
    <body>
        <h1>Add Account</h1>

        <!-- DISPLAY ERRORS -->

        <?php if ($getErrors): ?>
            <?php foreach ($getErrors as $e): ?>
                <?php echo "<p class='errors'>$e</p>"; ?>
            <?php endforeach ?>
        <?php endif; ?>


        <form action = "<?php echo $_SERVER['PHP_SELF'] ?>" method = "<?php echo strtoupper("post") ?>">
            <table>
                <tr>
                    <td>
                        <input type="text" name="username" id="username" placeholder="Enter a username"/> 
                    </td>
                </tr>

                <tr>
                    <td>
                        <input type="password" name="password" id="password" class = "password" placeholder="Enter a password"/>
                        <span class="showPassword" id ="showPassword">Show Password</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="submit" name="submit" id="submit" value="Submit"/>
                    </td>
                </tr>
            </table>
        </form>

        <div class="options">
            <div class="options-wrapper">
                <button type="button"><a href="updateAccount.php">Update Account</a></button>
                <button type="button"><a href="deleteAccount.php">Delete Account</a></button>
            </div>
        </div>

    </body>
    <script type="text/javascript">
        const { log } = console;

        var Password = document.getElementsByClassName("password")[0];

        var showPassword = document.getElementsByClassName("showPassword")[0];

        let Counter = 0;

        showPassword['addEventListener']('click', (e) => {

            Counter = Counter + 1;

            log (Counter);

            if (Counter >= 2)
            {
                Counter = 0;
            }

            if (Counter == 1)
            {
                Password.type = "text";
            } else
            {
                Password.type = "password";
            }
        })

    </script>
</html>