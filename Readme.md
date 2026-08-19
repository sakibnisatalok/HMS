
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

## Login execution flow

```
[ index.php ] --(User clicks 'Login/Register')--> [ public/login.php ]
                                                         |
                                        -----------------------------------
                                       |                                   |
                                 [ Login Form ]                    [ Register Form ]
                                       |                                   |
                             Enter Email & Password               Select Role (Doctor/Patient)
                                       |                          Enter Name, Email, Password
                                       |                                   |
                                        -----------------------------------
                                                         |
                                          (Form POSTs data to login.php)
                                                         |
                                              [ PHP Backend Processing ]
                                                         |
                            -----------------------------------------------------------
                           |                                                           |
                 [ If Action == Login ]                                     [ If Action == Register ]
                           |                                                           |
               Fetch user by email from DB                                 Hash the password securely
                           |                                                           |
               Verify password using hash                                  Insert new user into Database
                           |                                                           |
                 Check User's Role                                              Show Success Message
                           |                                                   (User can now log in)
        ---------------------------------------
        |                  |                  |
   Role = Admin       Role = Doctor      Role = Patient
        |                  |                  |
   Redirect to        Redirect to        Redirect to
 public/admin/       public/doctor/     public/patient/

```

