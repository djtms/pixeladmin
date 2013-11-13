<?php

abstract class PA_USER_TICKET extends PA_USER_TRACK {

    private $table;

    public function PA_USER_TICKET() {
        parent::PA_USER_TRACK();
        $this->table = $this->tables->user_ticket;
    }

    function openTicket($user_id, $ticket_type, $end_time = "000-00-00 00:00:00") {
        $ticket_key = sha1(randomString(20)) . sha1(randomString(20)) . sha1(randomString(20)) . sha1(randomString(20));

        if ($this->insert($this->table, array("user_id" => $user_id, "ticket_type" => $ticket_type, "ticket_key" => $ticket_key, "end_time" => $end_time))) {
            return $this->lastInsertId();
        }
        else
            return false;
    }

    function closeTicket($ticket_id) {
        $end_time = currentDateTime();

        return $this->execute("UPDATE {$this->table} SET status='closed', end_time=? WHERE ticket_id=?", array($end_time, $ticket_id));
    }

    function deleteTicket($ticket_id) {
        return $this->execute("DELETE FROM {$this->table} WHERE ticket_id=?", array($ticket_id));
    }

    function deleteUsersAllTickets($user_id) {
        $tickets = $this->listUsersAllTickets($user_id);
        $length = sizeof($tickets);

        for ($i = 0; $i < $length; $i++) {
            $this->deleteTicket($tickets[$i]->ticket_id);
        }

        return true;
    }

    function listUsersAllTickets($user_id) {
        return $this->get_rows("SELECT * FROM {$this->table} WHERE user_id=?", array($user_id));
    }

    function selectUserTicketsByTicketType($user_id, $ticket_type) {
        return $this->get_rows("SELECT * FROM {$this->table} WHERE user_id=? AND ticket_type=?", array($user_id, $ticket_type));
    }

    function closeTicketsByTicketType($user_id, $ticket_type) {
        $old_tickets = $this->selectUserTicketsByTicketType($user_id, $ticket_type);
        foreach ($old_tickets as $ot) {
            $this->closeTicket($ot->ticket_id);
        }

        return true;
    }

    function selectTicket($ticket_id) {
        return $this->get_row("SELECT * FROM {$this->table} WHERE ticket_id=?", array($ticket_id));
    }

    function validateTicket($user_id, $ticket_key, $ticket_type) {
        return $this->get_value("SELECT ticket_id FROM {$this->table} WHERE user_id=? AND ticket_type=? AND ticket_key=? AND status='active'", array($user_id, $ticket_type, $ticket_key));
    }

}