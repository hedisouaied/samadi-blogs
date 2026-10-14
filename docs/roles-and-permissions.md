# Roles and permissions

The back office uses a flat role model combined with a granular permission enum.
Roles are not stored in a separate table — a user has exactly one `role` string
column backed by the `UserRole` enum, and each role resolves to a set of
`Permission` cases.

## Roles

| Role | Value | Summary |
| --- | --- | --- |
| Super Admin | `super_admin` | Every permission. Only role that may assign the `super_admin` role. |
| Admin | `admin` | Full content and administration access, including user management, minus `super_admin` assignment. |
| Editor | `editor` | Manage and publish all content, taxonomy, banners and menus; moderate comments. Read-only users. |
| Author | `author` | Create and manage **their own** articles and submit them for review. Read-only taxonomy/comments/media listing. |
| Moderator | `moderator` | Comment moderation only. Read-only access to articles and users. |

Role descriptions are exposed to the UI via `UserRole::getDescription()`.

## Permissions

`App\Enums\Permission` defines the full permission surface, grouped by domain:

- **Panel**: `admin.access`
- **Users**: view / create / update / delete, plus `users.roles`
- **Articles**: view / create / delete, `articles.update.any`, `articles.update.own`,
  `articles.publish`, `articles.review`
- **Categories**: view / create / update / delete
- **Tags**: view / create / update / delete
- **Comments**: view / moderate / delete
- **Banners**: view / create / update / delete
- **Subscribers**: view / create / update / delete / export
- **Media**: view / create / delete
- **Menus**: view / manage
- **Settings**: view / manage
- **Activity**: view / delete

## How enforcement works

1. `UserRole::permissions()` returns the `Permission[]` for a role.
2. `User::hasPermission(Permission|string)` checks membership.
3. Every model has a policy (`app/Policies`) whose methods return
   `$user->hasPermission(...)`.
4. Filament resources/pages/actions additionally hide or disable UI based on the
   same checks, and `Settings::canAccess()` / resource `canViewAny()` gate whole
   screens.

> UI checks are a convenience only. The policies are the source of truth and are
> what protect the underlying actions.

## Ownership scoping

Authors hold `articles.update.own` instead of `articles.update.any`.
`ArticleResource::getEloquentQuery()` scopes non-`update.any` users to their own
articles, and the edit/delete policies compare `author_id` to the acting user.

## Assigning the super admin role

`super_admin` may only be assigned by a user who already holds
`Permission::ManageUserRoles`. The user create/edit pages strip the `super_admin`
option from the role select for everyone else, on top of the policy check.

## Adding a permission

1. Add a case to `App\Enums\Permission`.
2. Grant it to the relevant role(s) in `UserRole::permissions()`.
3. Use it in the relevant policy and, if needed, in the Filament resource/action.
