# Role-Based Access Control (RBAC) System

## Overview
The Fuwa.NG RBAC system provides granular access control for administrators, enabling the creation and management of custom roles (e.g., sub-admin, editor, blogger) and assigning specific permissions to these roles. It is built on top of the industry-standard `spatie/laravel-permission` package.

## Core Components
1. **Roles**: Groupings of permissions. Predefined roles include `super-admin`, `sub-admin`, `editor`, and `blogger`. Custom roles can be created via the Admin Dashboard.
2. **Permissions**: Specific access rights to modules (e.g., `manage_users`, `manage_content`, `manage_roles`).
3. **Admins**: The users of the system. An admin can be assigned multiple roles or direct permissions, although the standard approach is role-based.
4. **Super Admin**: A special boolean flag `is_super_admin` on the `admins` table. Super admins bypass all permission checks via a global Gate defined in `AppServiceProvider`.

## Database Schema
- `roles`: Stores the role definitions.
- `permissions`: Stores the permission definitions.
- `model_has_roles`: Pivot table linking `admins` to `roles`.
- `model_has_permissions`: Pivot table linking `admins` to `permissions`.
- `role_has_permissions`: Pivot table linking `roles` to `permissions`.

## Security & Privilege Escalation Prevention
To ensure sub-admins cannot abuse the system:
1. **Role Creation/Update**: When a sub-admin creates or updates a role, the system verifies that the sub-admin possesses all the permissions they are trying to assign to the new role. If they attempt to assign a permission they do not have, a `Privilege Escalation` error is thrown.
2. **Admin Management**: Sub-admins cannot create new super admins. They also cannot assign a role to an admin if that role contains permissions the sub-admin does not possess.
3. **Super Admin Protection**: Sub-admins cannot modify or delete the `super-admin` role, nor can they edit the profile of an existing super admin.

## API & Controller Endpoints
The RBAC system is managed via standard resourceful controllers located in `App\Http\Controllers\Admin`:
- `RoleController@index`: Lists all roles and their permission counts.
- `RoleController@store`: Creates a new role with assigned permissions.
- `RoleController@update`: Modifies an existing role.
- `RoleController@destroy`: Deletes a role (super-admin role is protected).

*Note: All actions in the `RoleController` and `AdminManagementController` are logged to the `admin_audit_logs` table.*

## Frontend Integration
The Admin Dashboard provides a visual matrix for assigning permissions to roles and roles to admins.
- **Roles Matrix**: Located at `resources/views/admin/roles/create.blade.php` and `edit.blade.php`.
- **Admin Assignment**: Located at `resources/views/admin/admins/create.blade.php` and `edit.blade.php`.

## Testing
Comprehensive test coverage is provided in `tests/Feature/RbacTest.php`, covering:
- Super admin unrestricted access.
- Sub-admin access based on assigned permissions.
- Privilege escalation prevention during role creation.
