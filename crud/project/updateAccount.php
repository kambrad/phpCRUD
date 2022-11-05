<?php
session_start();

ini_get("display_errors") == 1 ? ini_set("display_errors", 0) : ini_set("display_errors", 1);

error_reporting(E_ALL | E_PARSE);

include 'dataBase.php';

$updatePostNames = array("Name"=>$_POST['username'], 
                        "NewName"=>$_POST['new_username'], 
                        "NewPassword"=>$_POST['new_password'], 
                        "updateBtn"=>$_POST['update']
                    );

$errorsQueue = NULL;

$enableOrDisableAccountValue = $_POST['disableAccount'];


class updateErrors
{
    // Initiate And Setup Errors

    public $errors = [];

    // Set Errors just in case class varible is not declared

    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
class updateAccount extends updateErrors{

    private bool $_boolForError = TRUE;
    
    // public function __construct()
    // {
    //     $this->_boolForError = TRUE;
    // }

    public function __destruct()
    {
        // leave empty
    }

    public function checkForErrors(string $name, string $newName, string $newPassword): bool
    {
        $validFields = TRUE;

        if (empty($name) || empty($newName) || empty($newPassword)):
        
            $validFields = FALSE;

        endif;

        if (!isset($name) || !isset($newName) || !isset($newPassword)):

            $validFields = FALSE;

        endif;

        if (mb_strlen($newPassword) > 5):
        
            $validFields = FALSE;
        
        endif;

        return $validFields;
    }

    public function displayUpdatedErrors(string $name, string $newName, string $newPassword): array
    {

        $checkUpdateErrors = $this->getErrors();

        if ($this->checkForErrors($name, $newName, $newPassword) == (!$this->_boolForError))
        {
            array_push($checkUpdateErrors, "Required fields are empty or password exceeds maximum characters");
        }

        return $checkUpdateErrors;
    }

    public function getUpdateNameFromID(?string $name): ?int
    {
        global $database;

        $getName = htmlentities(stripslashes(trim($name)));

        if (empty($getName))
        {
            $getName = FALSE;
        }

        $rowID = NULL;

        $updateNameQueryForID = "SELECT crud.accounts.account_id FROM crud.accounts WHERE crud.accounts.account_name = ?";
        
        $updateNameQueryForIDPrepare = $database->prepare($updateNameQueryForID) or die("Error: " . $database->error);

        $updateNameQueryForIDPrepare->bind_param('s', $getName);

        if ($updateNameQueryForIDPrepare->execute() !== (!$this->_boolForError))
        {
            $rowIDFromName = $updateNameQueryForIDPrepare->get_result();

            $rowID = $rowIDFromName->fetch_assoc();

            $rowID = $rowID['account_id'];
        }
        return $rowID;
        
    }
    public function updateFieldAccount(string $name, string $newName, string $newPassword, bool $accountEnabled)
    {
        global $database;

        $getName = htmlentities(stripslashes(trim($name)));

        $getNewName = htmlentities(stripslashes(trim($newName)));

        $getNewPassword = htmlentities(stripslashes(trim($newPassword)));

        if (count($this->displayUpdatedErrors($name, $newName, $getNewPassword)) == 0)
        {
            if ($this->getUpdateNameFromID($getName) == $this->getUpdateNameFromID($getNewName))
            {
                throw new Exception("Username cannot change because username and new username are the same");
            }

            $updateTableQuery = "UPDATE crud.accounts SET account_name = ?, account_password = ?, account_enabled = ? WHERE account_id = ?";

            // Fetch ID from name to update 
            $updateNameFromID = $this->getUpdateNameFromID($getName);

            $updateHashedPassword = password_hash($getNewPassword, PASSWORD_DEFAULT); 

            $prepareUpdatedTableQuery = $database->prepare($updateTableQuery) or die("Error: " . $database->error);

            $prepareUpdatedTableQuery->bind_param('ssii', $getNewName, $updateHashedPassword, $accountEnabled, $updateNameFromID);

            if ($prepareUpdatedTableQuery->execute() == TRUE)
            {
                echo "<p class='success'>Account is updated</p>";
            }
            else
            {
                echo "<p class='error'>Account is not updated : " . $database->error . "</p>";  
            }




        }
    }


}
function checkIfAccountIsEnabledOrDisabled(): int
{
    // update account if new user wants to enable or disable the registered account
    global $enableOrDisableAccountValue;

    $getEnableDisableValue = NULL;

    if (isset($enableOrDisableAccountValue))
    {
        // echo "<pre>";
        //     print_r($enableOrDisableAccountValue);
        // echo "</pre>";

        if (($enableOrDisableAccountValue < 0) || ($enableOrDisableAccountValue > 1))
        {
            throw new Exception("Account cannot be enabled or disabled. ");
        }

        $getEnableDisableValue = $enableOrDisableAccountValue;
        
    }

    return $getEnableDisableValue;
}


function submitAccountBtn()
{
    global $updatePostNames, $errorsQueue;

    if (isset($updatePostNames['updateBtn']))
    {
        $updateAccount = new updateAccount();

        $errorsQueue = $updateAccount->displayUpdatedErrors($updatePostNames['Name'], $updatePostNames['NewName'], $updatePostNames['NewPassword']);

        // checkIfAccountIsEnabledOrDisabled() add to updateFieldAccount

        $updateAccount->updateFieldAccount($updatePostNames['Name'], $updatePostNames['NewName'], $updatePostNames['NewPassword'], checkIfAccountIsEnabledOrDisabled());
    }
}

submitAccountBtn();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Account</title>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700;800;900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
        *{
            font-family: 'Cinzel', sans-serif;
        }

        .updateContainer
        {
            text-align: center;
            position: relative;
            top: 150px;
        }

        .updateContainer form
        {
            display: flex;
            text-align: center;
            justify-content: center;
        }

        input[type='text'], input[type='password']
        {
            padding: 20px;
        }

        input[type='submit'] 
        {
                background-color: black;
                color: white;
                border: none;
                padding: 15px;
                cursor: pointer;
                width: 200px;
        }
        .options button
        {
                background-color: transparent;
                border: 1px solid black;
                padding: 5px;
                border-radius: 5px;
                cursor: pointer;
                margin: 10px;
                transition: all 250ms ease;
        }
        .options button a
        {
            color: black;
        }
        .error
        {
            color: red;
        }
        .success
        {
            color: green;
            position: absolute;
            bottom:0;
            left: 50%;
        }


    </style>
</head>
<body>
    <div class="updateContainer">
        <h1>Update Account</h1>

        <?php if ($errorsQueue): ?>
            <?php foreach($errorsQueue as $error): ?>
                <?php echo "<p class='error'>$error</p>"; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <form action = "<?php echo $_SERVER['PHP_SELF'] ?>" method = "<?php echo strtoupper('post'); ?>">
            <table>
                <tr>
                    <td>
                        <input type="text" name="username" class="username" placeholder="Enter username"/>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="text" name="new_username" class="new_username" placeholder="Enter new username"/>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="password" name="new_password" class="new_password" placeholder="Enter new password"/>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div id = "disableOption">
                            <p class="disable">If you would like to disable the account.</p>
                    
                            <input type="checkbox" name="checkToDisableAccount" class="checkToDisableAccount" min="0" max="1"/>
                        </div>
                    </td>
                    
                    <input type="hidden" name="disableAccount" class="disableAccount" value="1" min="0" max="1">
                </tr>

                <tr>
                    <td>
                        <input type="submit" name="update" class="update" value="Update"/>
                    </td>
                </tr>
            </table>
        </form>
        <div class="options">
            <button type="button"><a href="addAccount.php">Add Account</a></button>
            <button type="button"><a href="deleteAccount.php">Delete Account</a></button>
        </div>
    </div>
</body>
<script type='text/javascript'>

    const { log } = console;

    let checkToDisableAccount = document.getElementsByClassName("checkToDisableAccount")[0];

    let disableAccountValue = document.getElementsByClassName("disableAccount")[0];

    checkToDisableAccount['style']['cursor'] = 'pointer';

    checkToDisableAccount.addEventListener('click', (e) => {
        if (checkToDisableAccount.checked == true)
        {
            disableAccountValue.value = 0;
        } else
        {
            disableAccountValue.value = 1;
        }

        // log(disableAccountValue.value);
    })


</script>
</html>