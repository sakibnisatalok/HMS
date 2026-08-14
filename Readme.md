
# Hospital Management System




### web root 
> http://localhost/Hospitalmanagementsys_test/public/


## Codebase Structure

```
hospital-management/
│
├── public/
│   ├── login.php
│   ├── admin/
│   ├── doctor/
│   ├── patient/
│   ├── api/
│   └── assets/
│
├── app/
│   ├── config/
│   │   └── database.php
│   │
│   ├── functions/
│   │   ├── auth.php
│   │   ├── patients.php
│   │   ├── doctors.php
│   │   ├── appointments.php
│   │   └── medical_records.php
│   │
│   ├── middleware/
│       ├── auth.php
│       └── role.php
│
├── database/
│   ├── schema.sql
│   └── seed.sql
│
└── README.md
```