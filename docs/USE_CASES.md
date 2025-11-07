# Digital Claim System - Use Cases Documentation

## 🔹 **Overview of System Use Cases**

The **Digital Claim System** has two main actors:

1. **Employee** – submits and tracks claims.
2. **Admin (Finance personnel)** – manages users, verifies claims, and generates reports.

The system is organized into **five core modules**, each containing multiple use cases.

---

## 🧩 **1. User Management Module**

### **Actors:**
- **Employee**
- **Admin**

### **Use Cases and Descriptions:**

| No | Use Case                     | Actor           | Description                                                          |
| -- | ---------------------------- | --------------- | -------------------------------------------------------------------- |
| 1  | **Login Account**            | Employee, Admin | Secure login using registered email and password.                    |
| 2  | **Reset Password**           | Employee, Admin | Initiates password reset via secure email verification link or code. |
| 3  | **Update Password**          | Employee, Admin | Allows user to change an existing password when logged in.           |
| 4  | **Add User**                 | Admin only      | Creates a new user account in the system.                            |
| 5  | **Edit Profile**             | Admin           | Modifies user details like name, email, and role.                    |
| 6  | **Activate/Deactivate User** | Admin only      | Enables or disables a user's account; an extension of Edit Profile.  |

### **Key Functional Behavior:**
- Two-Factor Authentication (OTP verification) adds a layer of security.
- Admin exclusively controls user account creation and access privileges.
- Each account update or status change is validated and logged for security.

---

## 🧾 **2. Claim Submission Module**

### **Actors:**
- **Employee** (main actor)
- **Admin** (secondary observer; receives notifications)

### **Use Cases and Descriptions:**

| No | Use Case                      | Actor    | Description                                                 |
| -- | ----------------------------- | -------- | ----------------------------------------------------------- |
| 7  | **New Claim**                 | Employee | Starts a new expense claim submission.                      |
| 8  | **Fill in Claim Details**     | Employee | Inputs claim information such as type, amount, and purpose. |
| 9  | **Upload Receipt Attachment** | Employee | Uploads proof documents (JPEG, PNG, PDF).                   |
| 10 | **Submit Claim**              | Employee | Submits completed claim form to Admin for review.           |

### **Key Functional Behavior:**
- Validation ensures all mandatory fields and receipts are attached.
- System notifies Admin automatically upon claim submission.
- Admin can view claim requests and begin review workflow.

---

## 📊 **3. Claim Tracking Module**

### **Actors:**
- **Employee**
- **Admin**

### **Use Cases and Descriptions:**

| No | Use Case                  | Actor           | Description                                                                 |
| -- | ------------------------- | --------------- | --------------------------------------------------------------------------- |
| 11 | **View Submitted Claims** | Employee, Admin | Displays a list of claims and their statuses (Pending, Approved, Rejected). |
| 12 | **Edit Submitted Claims** | Employee        | Allows editing of claims that are pending or rejected.                      |

### **Key Functional Behavior:**
- Employees can track reimbursement progress.
- Admin can reference past claims for auditing.
- Edits are restricted to unapproved claims to maintain data integrity.

---

## 🧮 **4. Claim Review Module**

### **Actors:**
- **Admin** (primary)
- **Employee** (secondary, receives notifications)

### **Use Cases and Descriptions:**

| No | Use Case                  | Actor                | Description                                                  |
| -- | ------------------------- | -------------------- | ------------------------------------------------------------ |
| 13 | **View Pending Claims**   | Admin                | Lists all claims awaiting review.                            |
| 14 | **Approve/Reject Claims** | Admin                | Allows Admin to make decisions on submitted claims.          |
| 15 | **Notify Status**         | System (to Employee) | Sends claim status updates via email or system notification. |

### **Key Functional Behavior:**
- Each claim decision requires remarks for justification.
- Employees receive automatic status updates (approved/rejected).
- Ensures traceability and reduces manual communication errors.

---

## 📑 **5. Report Module**

### **Actors:**
- **Admin only**

### **Use Cases and Descriptions:**

| No | Use Case                      | Actor | Description                                                         |
| -- | ----------------------------- | ----- | ------------------------------------------------------------------- |
| 16 | **Generate Report**           | Admin | Compiles claim records into summaries.                              |
| 17 | **Export Report (PDF/Excel)** | Admin | Exports generated reports into digital formats for audit or backup. |
| 18 | **Print Report**              | Admin | Allows physical printing of reports for documentation.              |

### **Key Functional Behavior:**
- Filters reports by employee, date range, and claim type.
- Validation ensures criteria are complete before generation.
- Reports support both digital export and print options.

---

## 👥 **Actor Summary**

| Actor        | Description                                               | Key Functions                                                                     |
| ------------ | --------------------------------------------------------- | --------------------------------------------------------------------------------- |
| **Employee** | Regular staff member submitting claims for reimbursement. | Login, Reset/Update Password, Submit/Track/Edit Claims, Receive Notifications.    |
| **Admin**    | Finance team member managing claims and users.            | Add/Edit/Deactivate User, Review Claims, Approve/Reject, Generate/Export Reports. |

---

## ⚙️ **Interrelationships and System Behavior**

- **Employee → Admin Interaction:**
  Employees initiate claims → Admins receive notifications → Review decisions trigger system notifications.

- **Security Mechanisms:**
  All user functions (login, password change, claim submission) use validation and verification steps; only Admins have access to system-critical operations.

- **UML Relationships:**
  - **Include Relationships:** e.g., *Submit Claim* includes *Fill in Claim Details* and *Upload Receipt*.
  - **Extend Relationships:** *Activate/Deactivate User* extends *Edit Profile*; *Export Report* extends *Generate Report*.

---

## 📘 **Complete Use Case Summary (From Table 4.4)**

| No | Use Case                  | Description                                 |
| -- | ------------------------- | ------------------------------------------- |
| 1  | Login Account             | Log in using registered email and password. |
| 2  | Reset Password            | Request password reset through email.       |
| 3  | Update Password           | Change password from within system.         |
| 4  | Add User                  | Create new user (Admin only).               |
| 5  | Edit Profile              | Modify personal or user info.               |
| 6  | Activate/Deactivate User  | Enable or disable a user account.           |
| 7  | New Claim                 | Begin new reimbursement claim.              |
| 8  | Fill in Claim Details     | Input required claim data.                  |
| 9  | Upload Receipt Attachment | Attach receipt image or PDF.                |
| 10 | Submit Claim              | Send claim for review.                      |
| 11 | View Submitted Claims     | Check claim history and status.             |
| 12 | Edit Submitted Claims     | Revise claim before admin approval.         |
| 13 | View Pending Claims       | Admin views claims awaiting action.         |
| 14 | Approve/Reject Claims     | Admin approves or rejects claim.            |
| 15 | Notify Status             | Notify employee of decision.                |
| 16 | Generate Report           | Create claim summary report.                |
| 17 | Export Report (PDF/Excel) | Download report.                            |
| 18 | Print Report              | Print report for recordkeeping.             |

---

## 🔗 **Implementation Mapping**

### **Current Implementation Status:**
- ✅ **Use Cases 1-3**: Login, Password Reset/Update (Laravel Breeze)
- ✅ **Use Case 4-6**: User Management (Role-based middleware and admin functions)
- 🔄 **Use Cases 7-10**: Claim Submission (Phase 2 development)
- 🔄 **Use Cases 11-12**: Claim Tracking (Phase 2 development)
- 🔄 **Use Cases 13-15**: Claim Review (Phase 2 development)
- ⏳ **Use Cases 16-18**: Reporting (Phase 4 development)

### **Technical Implementation Notes:**
- Authentication system supports secure login and password management
- Role-based access control implemented via middleware
- Database schema supports all use case requirements
- Audit logging system ready for compliance tracking

---

**Document Version:** 1.0
**Last Updated:** November 2025
**Source:** FYP Project Document Chapter 4