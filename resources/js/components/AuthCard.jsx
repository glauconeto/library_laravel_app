import React from 'react';

export default function AuthCard({ children, className = '' }) {
    return (
        <div className={`min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 px-4 py-12 sm:px-6 lg:px-8 ${className}`}>
            <div className="w-full max-w-md space-y-8">
                {children}
            </div>
        </div>
    );
}