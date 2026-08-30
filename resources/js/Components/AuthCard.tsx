import React, { HTMLAttributes } from 'react';

interface AuthCardProps extends HTMLAttributes<HTMLDivElement> {
    children: React.ReactNode;
    className?: string;
}

export default function AuthCard({ children, className = '', ...props }: AuthCardProps) {
    return (
        <div className={`min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 px-4 py-12 sm:px-6 lg:px-8 ${className}`} {...props}>
            <div className="w-full max-w-md space-y-8">
                {children}
            </div>
        </div>
    );
}