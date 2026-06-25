<?php http_response_code(403); exit; ?>
{
    "users": [
        {
            "id": 1,
            "username": "admin",
            "password_hash": "$2y$10$qg.L5dajYtYpb6c3yiZgku59JQnBqfwNWcOiCPwFSzRzsNGEHurMa",
            "name": "faruk guler",
            "role": "Administrator",
            "title": "",
            "avatar": "avatars\/user_1_1782110031.png",
            "email": "admin@hopper.local",
            "phone": "",
            "department": "Management",
            "status": "Working",
            "group": "System Administration"
        },
        {
            "id": 2,
            "username": "approver",
            "password_hash": "$2y$10$qg.L5dajYtYpb6c3yiZgku59JQnBqfwNWcOiCPwFSzRzsNGEHurMa",
            "name": "David Miller",
            "role": "CAB Approver",
            "title": "Senior CAB Member",
            "avatar": "",
            "email": "approver_new@hopper.local",
            "phone": "",
            "department": "IT Operations",
            "status": "",
            "group": "Network & Security"
        },
        {
            "id": 3,
            "username": "requester",
            "password_hash": "$2y$10$qg.L5dajYtYpb6c3yiZgku59JQnBqfwNWcOiCPwFSzRzsNGEHurMa",
            "name": "Developer Alice",
            "role": "Requester",
            "title": "Software Engineer",
            "avatar": "",
            "email": "alice@hopper.local",
            "phone": "",
            "department": "Technical Service",
            "status": "",
            "group": "Software Development"
        },
        {
            "id": 4,
            "username": "ahmer",
            "password_hash": "$2y$10$qg.L5dajYtYpb6c3yiZgku59JQnBqfwNWcOiCPwFSzRzsNGEHurMa",
            "name": "ahmer",
            "role": "Requester",
            "title": "IT Operations",
            "department": "IT Operations",
            "email": "",
            "phone": "",
            "avatar": "avatars\/user_4_1782133229.jpg",
            "status": "",
            "group": "DevOps"
        }
    ],
    "changes": [
        {
            "id": "CHG-1001",
            "title": "ayluk update",
            "description": "ayluk update",
            "requester": "faruk guler",
            "requesterTitle": "IT Operations",
            "owner": "faruk guler",
            "ownerTitle": "Owner",
            "category": "Cloud Infrastructure",
            "risk": "Low",
            "status": "Draft",
            "targetDate": "2026-06-23",
            "impact": "ayluk update",
            "rollbackPlan": "ayluk update",
            "tasks": [
                {
                    "id": 1,
                    "text": "ayluk update",
                    "completed": false
                }
            ],
            "progress": 0,
            "approvals": [
                {
                    "role": "CAB (Change Advisory Board)",
                    "status": "Pending",
                    "date": ""
                }
            ],
            "comments": [],
            "assignedGroup": "System Administration",
            "revisions": [],
            "ownerUsername": "admin",
            "requesterUsername": "admin"
        }
    ],
    "activities": [
        {
            "id": 1782223726074,
            "user": "faruk guler",
            "action": "updated their profile settings",
            "target": "USR-1",
            "date": "2026-06-23 16:08"
        },
        {
            "id": 1782222597340,
            "user": "faruk guler",
            "action": "updated their profile settings",
            "target": "USR-1",
            "date": "2026-06-23 15:49"
        },
        {
            "id": 1782222579917,
            "user": "faruk guler",
            "action": "updated their profile settings",
            "target": "USR-1",
            "date": "2026-06-23 15:49"
        },
        {
            "id": 1782202180491,
            "user": "faruk guler",
            "action": "added a new department: \"fff\"",
            "target": "DEPT-NEW",
            "date": "2026-06-23 10:09"
        },
        {
            "id": 1782133229278,
            "user": "ahmer",
            "action": "updated their profile settings",
            "target": "USR-4",
            "date": "2026-06-22 15:00"
        },
        {
            "id": 1782133199311,
            "user": "faruk guler",
            "action": "deleted the change request \"ayluk update\".",
            "target": "CHG-1002",
            "date": "2026-06-22 14:59"
        },
        {
            "id": 1782133193413,
            "user": "faruk guler",
            "action": "deleted attachment 'hopper.png'",
            "target": "CHG-1002",
            "date": "2026-06-22 14:59"
        },
        {
            "id": 1782133182464,
            "user": "faruk guler",
            "action": "reset the change request to draft for testing.",
            "target": "CHG-1002",
            "date": "2026-06-22 14:59"
        },
        {
            "id": 1782133054580,
            "user": "faruk guler",
            "action": "marked the change request as completed successfully.",
            "target": "CHG-1002",
            "date": "2026-06-22 14:57"
        },
        {
            "id": 1782133051767,
            "user": "faruk guler",
            "action": "started the change implementation in production.",
            "target": "CHG-1002",
            "date": "2026-06-22 14:57"
        },
        {
            "id": 1782133049229,
            "user": "faruk guler",
            "action": "approved the change request.",
            "target": "CHG-1002",
            "date": "2026-06-22 14:57"
        },
        {
            "id": 1782133047205,
            "user": "faruk guler",
            "action": "submitted the change request for approval.",
            "target": "CHG-1002",
            "date": "2026-06-22 14:57"
        },
        {
            "id": 1782133039150,
            "user": "faruk guler",
            "action": "submitted the change request for review.",
            "target": "CHG-1002",
            "date": "2026-06-22 14:57"
        },
        {
            "id": 1782133037314,
            "user": "faruk guler",
            "action": "uploaded attachment 'hopper.png'",
            "target": "CHG-1002",
            "date": "2026-06-22 14:57"
        },
        {
            "id": 1782133016389,
            "user": "ahmer",
            "action": "updated their profile settings",
            "target": "USR-4",
            "date": "2026-06-22 14:56"
        },
        {
            "id": 1782132987870,
            "user": "faruk guler",
            "action": "added a comment: \"yapma kardeim...\"",
            "target": "CHG-1002",
            "date": "2026-06-22 14:56"
        },
        {
            "id": 1782132978517,
            "user": "faruk guler",
            "action": "updated task status: \"ayluk update\" (Pending)",
            "target": "CHG-1002",
            "date": "2026-06-22 14:56"
        },
        {
            "id": 1782132977301,
            "user": "faruk guler",
            "action": "updated task status: \"ayluk update\" (Completed)",
            "target": "CHG-1002",
            "date": "2026-06-22 14:56"
        },
        {
            "id": 1782132938866,
            "user": "sdsd",
            "action": "created a new change request: \"ayluk update\"",
            "target": "CHG-1002",
            "date": "2026-06-22 14:55"
        },
        {
            "id": 1782132833294,
            "user": "faruk guler",
            "action": "created a new change request: \"ayluk update\"",
            "target": "CHG-1001",
            "date": "2026-06-22 14:53"
        },
        {
            "id": 1782132164984,
            "user": "faruk guler",
            "action": "updated their profile settings",
            "target": "USR-1",
            "date": "2026-06-22 14:42"
        },
        {
            "id": 1782129072619,
            "user": "faruk guler",
            "action": "updated their profile settings",
            "target": "USR-1",
            "date": "2026-06-22 13:51"
        },
        {
            "id": 1782110031585,
            "user": "Administrator",
            "action": "updated their profile settings",
            "target": "USR-1",
            "date": "2026-06-22 08:33"
        },
        {
            "id": 1781876684852,
            "user": "Administrator",
            "action": "updated user details for 'approver'",
            "target": "USR-2",
            "date": "2026-06-19 15:44"
        },
        {
            "id": 1781876584414,
            "user": "Administrator",
            "action": "updated user details for 'admin'",
            "target": "USR-1",
            "date": "2026-06-19 15:43"
        },
        {
            "id": 1781876544031,
            "user": "Administrator",
            "action": "updated user details for 'approver'",
            "target": "USR-2",
            "date": "2026-06-19 15:42"
        },
        {
            "id": 1781876153693,
            "user": "Administrator",
            "action": "deleted department: \"Customer Services\"",
            "target": "DEPT-DEL",
            "date": "2026-06-19 15:35"
        },
        {
            "id": 1781876151833,
            "user": "Administrator",
            "action": "deleted change category: \"System & Server\"",
            "target": "CAT-DEL",
            "date": "2026-06-19 15:35"
        },
        {
            "id": 1781876149791,
            "user": "Administrator",
            "action": "deleted change category: \"Database Management\"",
            "target": "CAT-DEL",
            "date": "2026-06-19 15:35"
        },
        {
            "id": 1781875410573,
            "user": "Administrator",
            "action": "approved registration request for 'ahmer'",
            "target": "USR-4",
            "date": "2026-06-19 15:23"
        }
    ],
    "categories": [
        "Software Development",
        "Network & Security",
        "Cloud Infrastructure",
        "Hardware & Infrastructure"
    ],
    "registration_requests": [
        {
            "id": 1781871585943,
            "username": "ahmet",
            "password_hash": "$2y$10$qg.L5dajYtYpb6c3yiZgku59JQnBqfwNWcOiCPwFSzRzsNGEHurMa",
            "name": "ahmet demir",
            "role": "CAB Approver",
            "title": "solver",
            "department": "Security",
            "email": "",
            "phone": "",
            "avatar": "",
            "status": "Rejected",
            "request_date": "2026-06-19 14:19",
            "group": ""
        },
        {
            "id": 1781875395387,
            "username": "ahmer",
            "password_hash": "$2y$10$qg.L5dajYtYpb6c3yiZgku59JQnBqfwNWcOiCPwFSzRzsNGEHurMa",
            "name": "sdsd",
            "role": "Requester",
            "title": "IT Operations",
            "department": "IT Operations",
            "email": "",
            "phone": "",
            "avatar": "",
            "status": "Approved",
            "request_date": "2026-06-19 15:23",
            "group": "IT Operations"
        }
    ],
    "departments": [
        "Management",
        "IT Operations",
        "Human Resources",
        "Accounting",
        "Sales",
        "Marketing",
        "R&D",
        "Logistics",
        "Warehouse",
        "Security",
        "Technical Service",
        "Quality Control",
        "Training",
        "Purchasing",
        "Finance & Accounting",
        "fff"
    ],
    "groups": [
        "Software Development",
        "Database Administration",
        "Network & Security",
        "System Administration",
        "DevOps"
    ],
    "notification_settings": {
        "webhookUrl": "",
        "notifyOnCreate": true,
        "notifyOnStatusChange": true,
        "notifyOnHighRiskOnly": false
    }
}