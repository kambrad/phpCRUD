<?php

include 'dataBase.php';

error_reporting(E_ERROR | E_PARSE);

$deletePostNames = array("Name"=>$_POST['username'], "Password"=>$_POST['password'],"DeleteBtn"=>$_POST['delete']);

$deleteIDValue = $_POST['deleteID'];

$getErrorsForDeletion = NULL;

class deleteErrors
{
    public array $errors = [];

    public function getDeleteErrors(): array
    {
        return $this->errors;
    }
}

class deleteAccount extends deleteErrors
{
    public function _destruct()
    {
        // leave empty
    }

    public function checkNameError(string $name): bool
    {
        $validNameError = TRUE;

        if (empty($name)):

            $validNameError = FALSE;

        endif;

        return $validNameError;
    }

    public function checkPasswordError(string $password) : bool
    {
        $validPasswordError = TRUE;

        if (empty($password))
        {
            $validPasswordError = FALSE;
        }

        return $validPasswordError;
    }

    public function getIDFromName(string $name): ?int
    {
        global $database;

        $getName = htmlentities(stripslashes(trim($name)));

        $rowIDToBeDeleted = NULL;

        if ($this->checkNameError($getName) == FALSE)
        {
            throw new Exception ("Name is empty");
        }

        $deleteNameQueryForId = "SELECT crud.accounts.account_id FROM crud.accounts WHERE crud.accounts.account_name = ?";

        $deleteNamePrepareQuery = $database->prepare($deleteNameQueryForId) or die("Error with query :" . $database->error);

        $deleteNamePrepareQuery->bind_param('s', $getName);

        $deleteNamePrepareQuery->execute();

        $getResultFromDeleteNameQuery = $deleteNamePrepareQuery->get_result();

        $fetchRowIDToBeDeleted = $getResultFromDeleteNameQuery->fetch_assoc();

        $rowIDToBeDeleted = $fetchRowIDToBeDeleted['account_id'];

        return $rowIDToBeDeleted;
    }

    public function displayErrors(string $name, string $password): array
    {
        $getErrors = $this->getDeleteErrors();

        if ($this->checkNameError($name) == FALSE)
        {
            array_push($getErrors, "Please enter name");
        }

        if ($this->checkPasswordError($password) == FALSE)
        {
            array_push($getErrors, "Please enter password");
        }

        return $getErrors;
    }

    // return id that is deleted from account.

    public function deleteAccount(string $name, string $password)
    {
        global $database;

        $getName = htmlentities(stripslashes(trim($name)));

        $getPassword = htmlentities(stripslashes(trim($password)));

        $returnID = NULL;

        if ($this->checkNameError($getName) == FALSE)
        {
            throw new Exception("Name is empty");
        }

        if ($this->checkPasswordError($getPassword) == FALSE)
        {
            throw new Exception("Password is empty");
        }

        $idFromName = $this->getIDFromName($getName);

        $getIdFromName = "SELECT * FROM crud.accounts WHERE crud.accounts.account_name= ? OR crud.accounts.account_password = ?";

        $getIdFromNameQuery = $database->prepare($getIdFromName) or die("Error with query " . $database->error);

        // $hashedPassword = password_verify($getPassword, );
        $getIdFromNameQuery->bind_param('ss', $getName, $getPassword);
        $getIdFromNameQuery->execute();
        
        $getIdFromNameResult = $getIdFromNameQuery->get_result();

        $rowsIDName = $getIdFromNameResult->fetch_assoc();

        if (password_verify($getPassword, $rowsIDName['account_password']))
        {
            if ($idFromName == $rowsIDName['account_id'])
            {
                $deleteFromTable = "DELETE FROM crud.accounts WHERE crud.accounts.account_id = ?";

                $deleteFromTableQuery = $database->prepare($deleteFromTable);
                $deleteFromTableQuery->bind_param("i", $idFromName);
                $deleteFromTableQuery->execute();

                echo "The ID: " . $idFromName . " is deleted from table";
            } else
            {
                echo "Username does not exists => " . $idFromName . " : " . $rowsIDName['account_id'];
            }
            
        } else
        {
            echo "False : " . $getPassword . " => " . $rowsIDName['account_password'];
        }


    }
}


try
{
    global $database, $deletePostNames, $getErrorsForDeletion;

    if (isset($deletePostNames['DeleteBtn']))
    {
        $getName = $deletePostNames['Name'];
        $getPassword = $deletePostNames['Password'];

        $deleteAccount = new deleteAccount();

        $getErrorsForDeletion = $deleteAccount->displayErrors($getName, $getPassword);

        // $getDeletedFromTable = $deleteAccount->deleteAccount($getName, $getPassword);
        $deleteAccount->deleteAccount($getName, $getPassword);

    } 
} catch (Exception $e)
{
   //  throw new $e->getMessage("Error");
}


// echo "The Row ID: " . $getDeletedFromTable . " is deleted.";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700;800;900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

        *
        {
            font-family: 'Cinzel', sans-serif;
        }
        .deleteContainer
        {
            text-align: center;
            position: relative;
            top: 150px;
        }

        .deleteContainer form
        {
            display: flex;
            text-align: center;
            justify-content: center;
        }
        #password
        {
            z-index: 0;
            position: relative;
            /* bottom: 80px; */
            transform: translateY(-80px);
            display: none;
        }
        input[type='password']
        {
            position: relative;
            z-index: 0;
        }
        input[type='text'], input[type='password']
        {
            padding: 20px;
            margin: 6px;
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

    </style>
</head>
<body>
    <div class="deleteContainer">
        <h1>Delete Account</h1>

        <?php if ($getErrorsForDeletion): ?>
            <?php foreach($getErrorsForDeletion as $error): ?>
                <?php echo "<p class='error'>$error</p>"; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="<?php echo strtoupper("POST") ?>">
            <table>
                <tr>
                    <td id="username">
                        <input type="text" name="username" class="username" placeholder="Enter username"/>
                    </td>
                </tr>
                <tr>
                    <td id="password">
                        <input type="password" name="password" class="password" placeholder="Confirm password"/>
                    </td>
                </tr>
                <tr>
                    <td id="submit">
                        <input type="submit" name="delete" class="delete" value="Continue"/>
                        <input type="hidden" name="deleteID" class="deleteID" value="0"/>
                    </td>
                </tr>
            </table>
        </form>
        <div class="options">
            <button type="button"><a href="addAccount.php">Add Account</a></button>
            <button type="button"><a href="updateAccount.php">Update Account</a></button>
        </div>
    </div>
    
</body>
<script type="text/javascript">

    const { log } = console;

    let Username = document.getElementsByClassName("username")[0];

    let Password = document.getElementById("password");

    let deleteID = document.getElementsByClassName('deleteID')[0];

    let submitBtn = document.getElementsByClassName("delete")[0];

    var setCounter = 0;

    function displayUsernameField(username)
    {
        let returnUsernameError = true;

        username.addEventListener('input', (e) => {
            if (username.value == "")
            {
                returnUsernameError = false;
            }

        });

        return returnUsernameError;
    }

    function displayPasswordField(password)
    {
        let getPassword = password == !true ? false : password;

        getPassword.style.display = 'flex';

        getPassword.style.transform = "translateY(0px)";
        getPassword.style.transition = "transform 650ms ease-in";

    }

    submitBtn.addEventListener('click', (e) => {

        if (setCounter === 0)
        {
            e.preventDefault();
            setCounter = setCounter + 1;

            deleteID.value = 1;

            if (displayUsernameField(Username) == false)
            {
                var errorMsg = new Error;
                
                alert(`Alert error: ${errorMsg.message}`);

                submitBtn.value = "Continue";
            } else
            {
                displayPasswordField(Password);

                submitBtn.value = "Delete Account";
            }

            
        } else {

            setCounter = 0;

            deleteID.value = 0;
        }

        // log (setCounter);
        // log(deleteID.value);
        
    })
</script>
</html>

