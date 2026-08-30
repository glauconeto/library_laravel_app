import React from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';
import LibrarianDashboard from './Dashboard/Librarian';
import UserDashboard from './Dashboard/User';
import { User } from '@/Types';

interface DashboardProps {
    auth: {
        user: User;
    };
}

export default function Dashboard({ auth, ...props }: DashboardProps) {
    const user = auth.user;

    // Check if user has librarian role
    const isLibrarian = user.roles && user.roles.some((role) => role.name === 'librarian');

    return (
        <AppLayout user={user}>
            {isLibrarian ? (
                <LibrarianDashboard {...props} />
            ) : (
                <UserDashboard {...props} />
            )}
        </AppLayout>
    );
}