<?php
class log
{
    private $application;
    private $warning;
    private $suspicious_data;
    private $word;
    private $word2;
    private $source;
    
    function addApplication()
    {
        global $bdd;
        
        $update = $bdd->prepare("UPDATE notification SET application = application + '1'");
        $update->execute();
    }
    
    function addWarning()
    {
        global $bdd;
        
        $update = $bdd->prepare("UPDATE notification SET warning = warning + '1'");
        $update->execute();
    }
    
    function setSource($source)
    {
        $this->source = $source;
    }
    
    function show_log_dangerous()
    {
        global $bdd;
        $select_log = $bdd->prepare("SELECT * FROM logs WHERE warning = 'Dangerous'ORDER BY warning DESC LIMIT 0,50");
        $select_log->execute();
        while($result = $select_log->fetch())
        {
            $tr = "";
            if($result['warning'] == 'suspicious')
                $tr = 'warning';
            elseif($result['warning'] == "Dangerous")
                $tr = 'negative';
            ?>
                <tr class="<?php echo $tr; ?>">
                  <td><?php echo $result['file_link']; ?></td>
                  <td><?php echo $result['warning']; ?></td>
                  <td><a href='?show=<?php echo $result['id']; ?>'>Show</a></td>
                </tr>
            <?php
        }
    }

    function showID($id)
    {
        global $bdd;
        $select = $bdd->prepare("SELECT * FROM logs WHERE id = :id");
        $select->bindParam(':id', $id);
        $select->execute();
        $result = $select->fetch();
        $word = $result['word'];
        $word2 = $result['word2'];
        $source = base64_decode($result['sourcecode']);
        echo "<div class='word'>Suspect word : ".$result['word']."</div>";
        echo "<div class='word'>Suspect word : ".$result['word2']."</div>";
        $str = str_replace($word,"SALOPE", $source);
        $str = str_replace($word2,"SALOPE", $source);
        echo "<div class='word'>Suspect found : ".base64_decode($result['grep_information'])."</div>";
        echo "<div class='scode'>source_code : <textarea class='codesource'>".$source."</textarea></div>";
    }
    
    function checkId($id)
    {
        global $bdd;
        $select = $bdd->prepare("SELECT id FROM logs WHERE id = :id");
        $select->bindParam(':id', $id);
        $select->execute();
        $result = $select->fetch();
        if($result['id'] == '')
            exit(1);
    }
    
    function show_log_suspicious()
    {
        global $bdd;
        $select_log = $bdd->prepare("SELECT * FROM logs WHERE warning = 'suspicious'ORDER BY warning DESC LIMIT 0,50");
        $select_log->execute();
        while($result = $select_log->fetch())
        {
            $tr = "";
            if($result['warning'] == 'suspicious')
                $tr = 'warning';
            elseif($result['warning'] == "Dangerous")
                $tr = 'negative';
            ?>
                <tr class="<?php echo $tr; ?>">
                  <td><?php echo $result['file_link']; ?></td>
                  <td><?php echo $result['warning']; ?></td>
                  <td><a href='?show=<?php echo $result['id']; ?>'>Show</a></td>
                </tr>
            <?php
        }
    }
    
    function show_log_basic()
    {
        global $bdd;
        $select_log = $bdd->prepare("SELECT * FROM logs WHERE warning = 'basic'ORDER BY warning DESC LIMIT 0,50");
        $select_log->execute();
        while($result = $select_log->fetch())
        {
            $tr = "";
            if($result['warning'] == 'suspicious')
                $tr = 'warning';
            elseif($result['warning'] == "Dangerous")
                $tr = 'negative';
            ?>
                <tr class="<?php echo $tr; ?>">
                  <td><?php echo $result['file_link']; ?></td>
                  <td><?php echo $result['warning']; ?></td>
                  <td><a href='?show=<?php echo $result['id']; ?>'>Show</a></td>
                </tr>
            <?php
        }
    }
    
    function show_log()
    {
        global $bdd;
        $select_log = $bdd->prepare("SELECT * FROM logs ORDER BY warning DESC LIMIT 0,50");
        $select_log->execute();
        while($result = $select_log->fetch())
        {
            $tr = "";
            if($result['warning'] == 'suspicious')
                $tr = 'warning';
            elseif($result['warning'] == "Dangerous")
                $tr = 'negative';
            ?>
                <tr class="<?php echo $tr; ?>">
                  <td><?php echo $result['file_link']; ?></td>
                  <td><?php echo $result['warning']; ?></td>
                  <td><a href='show.php?id=<?php echo $result['id']; ?>'>Show</a></td>
                </tr>
            <?php
        }
    }
    
    function insertLog()
    {
        global $bdd;
        
        if($this->application != '' && $this->warning != '' && $this->suspicious_data != '')
        {
            $select  = $bdd->prepare("SELECT * FROM logs WHERE file_link = :file_link");
            $select->bindParam(':file_link', $this->application);
            $select->execute();
            
            $result = $select->fetch();
            if($result['id'] == '')
            {
                if($this->warning == "Dangerous")
                    $this->addWarning();
                if($this->warning == "suspicious")
                    $this->addWarning();
                if($this->word == '')
                    $this->word = "not found";
                if($this->word2 == '')
                    $this->word2 = "not found";
                $insert = $bdd->prepare("INSERT INTO logs(file_link, warning, grep_information,word,word2,sourcecode) VALUES(:file_link, :warning, :grepinfo, :word,:word2,:source)");
                $insert->bindParam(':file_link', $this->application);
                $insert->bindParam(':warning', $this->warning);
                $insert->bindParam(':grepinfo', $this->suspicious_data);
                $insert->bindParam(':word', $this->word);
                $insert->bindParam(':word2', $this->word2);
                $insert->bindParam(':source', $this->source);
                $insert->execute();
                $this->addApplication();
            }
            else
            {
                if($result['warning'] != $this->warning && $result['warning'] != 'Dangerous')
                {
                    if($this->word2 == '')
                        $this->word2 = "not found";
                    $update = $bdd->prepare("UPDATE logs SET warning = :warning, word2 = :word2 WHERE file_link = :file_link");
                    $update->bindParam(':warning', $this->warning);
                    $update->bindParam(':file_link', $this->application);
                    $update->bindParam(':word2', $this->word2);
                    $update->execute();
                    $this->addWarning();
                }
            }
        }
    }
    
    
    function getApplication()
    {
        global $bdd;
        
        $select = $bdd->prepare("SELECT application FROM notification");
        $select->execute();
        $result = $select->fetch();
        echo $result['application'];
    }
    
    function getWarning()
    {
        global $bdd;
        
        $select = $bdd->prepare("SELECT warning FROM notification");
        $select->execute();
        $result = $select->fetch();
        echo $result['warning'];
    }
    
    function setWord2($word)
    {
        $this->word2 = $word;
    }
    
    function setWord($word)
    {
        $this->word = $word;
    }
    
    function setApplication($application)
    {
        $this->application = $application;
    }
    
    function setWarning($warning)
    {
        $this->warning = $warning;
    }
    
    function setSuspicious($suspicious_data)
    {
        $this->suspicious_data = $suspicious_data;
    }
}