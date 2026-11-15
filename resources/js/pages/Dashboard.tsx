import AppLayout from '@/layouts/AppLayout';
import LibrarianDashboard from './Dashboard/Librarian';
import UserDashboard from './Dashboard/User';
import { User } from '@/Types';

interface DashboardProps {
    auth: {
        user: User;
    };
    // passthrough props for either dashboard variant
    [key: string]: unknown;
}

export default function Dashboard({ auth, ...props }: DashboardProps) {
    const user = auth.user;

    // Check if user has librarian role
    const isLibrarian = user.roles && user.roles.some((role) => role.name === 'librarian');

    return (
        <AppLayout user={user}>
            {isLibrarian ? (
                <LibrarianDashboard {...(props as unknown as React.ComponentProps<typeof LibrarianDashboard>)} />
            ) : (
                <UserDashboard {...(props as unknown as React.ComponentProps<typeof UserDashboard>)} />
            )}
        </AppLayout>
    );
}