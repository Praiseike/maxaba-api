<?php

    namespace App\Enums;

    enum Status: string {
        case ACTIVE = "active";
        case INACTIVE = "inactive";
        case PENDING = "pending";
        case APPROVED = "approved";
        case REJECTED = "rejected";
        case SUSPENDED = "suspended";
    }