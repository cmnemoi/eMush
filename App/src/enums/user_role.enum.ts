import { User } from "@/entities/User";

export enum UserRole {
    SUPER_ADMIN = 'ROLE_SUPER_ADMIN',
    ADMIN = 'ROLE_ADMIN',
    MODERATOR = 'ROLE_MODERATOR',
    USER = 'ROLE_USER',
}

export function is_granted(role:UserRole, user:User) : boolean
{
    switch (role) {
    case UserRole.SUPER_ADMIN:
        return user.roles.includes(UserRole.SUPER_ADMIN);
    case UserRole.ADMIN:
        return  user.roles.filter((value:UserRole) => [UserRole.ADMIN, UserRole.SUPER_ADMIN].includes(value)).length > 0;
    case UserRole.MODERATOR:
        return  user.roles.filter((value:UserRole) => [UserRole.ADMIN, UserRole.SUPER_ADMIN, UserRole.MODERATOR].includes(value)).length > 0;
    case UserRole.USER:
        return  user.roles.filter((value:UserRole) => [UserRole.ADMIN, UserRole.SUPER_ADMIN, UserRole.MODERATOR, UserRole.USER].includes(value)).length > 0;
    default:
        return false;
    }
}
