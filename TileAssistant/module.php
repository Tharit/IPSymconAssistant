<?php


class TileAssistant extends IPSModule
{

    public function Create()
    {
         // Never delete this line!
         parent::Create();

         $this->RegisterPropertyInteger('message', 0);
         $this->RegisterPropertyInteger('log', 0);
         $this->SetVisualizationType(1);
    }

    public function ApplyChanges() {
        parent::ApplyChanges();

        foreach ($this->GetMessageList() as $senderID => $messageIDs) {
            foreach($messageIDs as $messageID) {
                $this->UnregisterMessage($senderID, $messageID);
            }
        }

        $this->RegisterMessage($this->ReadPropertyInteger('log'), VM_UPDATE);
    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
        $objectId = $this->ReadPropertyInteger('log');
        if ($SenderID !== $objectId) return;
        $this->UpdateVisualizationValue(GetValue($objectId));
    }

    public function RequestAction($Ident, $Value)
    {
	    if($Ident === 'message') {
            $script = $this->ReadPropertyInteger('message');
            if($script && @IPS_GetScript($script)) {
                IPS_RunScriptEx($script, json_decode($Value, true));
            }
        } else if($Ident === 'load') {
            $data = json_decode($Value, true);
            // $data['session'] and $data['speaker'] can be used to restore the session and user in the script

            // The official Archive Handler / Archive Control GUID in IP-Symcon
            $archiveGuid = "{43192F0B-135B-4CE7-A0A7-1475603F3060}";

            // Get an array of all instance IDs matching this module GUID
            $archiveInstances = IPS_GetInstanceListByModuleID($archiveGuid);

            // Grab the first instance ID from the array
            if (empty($archiveInstances)) {
                return; // No instance found, handle this case as needed
            }
            $idArchive = $archiveInstances[0];
            $idMessageLog = $this->ReadPropertyInteger('log');
            $values = AC_GetLoggedValues($idArchive, $idMessageLog, time() - 15 * 60, time(), 10);
            $res = json_encode(
                array_reverse(
                    array_values(
                        array_map(function($item) {
                            return $item['Value'];
                        }, $values)
                    )
                )
            );    
            $this->UpdateVisualizationValue($res);
        }
    }

    public function GetVisualizationTile() {
        $module = file_get_contents(__DIR__ . '/module.html');
        return $module;
    }
}
