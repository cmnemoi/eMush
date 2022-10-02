# RoomLog
This module handle the logs displayed in the room.
It listens to all other modules and creates the logs corresponding to the event.

# Architecture 

## Directory Tree:
    |-- config
    |-- Controller
    |-- Entity
    |-- Enum
    |-- Event
    |-- Listener
    |-- Repository
    |-- Service

# Usage
When an action, or an event is being performed, a roomLog should be created  
In order to add a roomLog, use the RoomLogService::createLog method
```
public function createLog(string $logKey, Player $player, Room $room, string $visibility, RoomLogParameter $roomLogParameter): RoomLog;
```

