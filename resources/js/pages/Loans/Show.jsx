import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../layouts/AppLayout';
import PrimaryButton from '../../Components/PrimaryButton';

export default function LoansShow({ loan, isOverdue, daysOverdue }) {
    return (
        <AppLayout>
            <Head title={`Loan Details - ${loan.book.title}`} />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                        <div className="p-6">
                            {/* Header */}
                            <div className="flex justify-between items-start mb-6">
                                <div>
                                    <h1 className="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                        Loan Details
                                    </h1>
                                    <p className="text-gray-600 dark:text-gray-400">
                                        Loan ID: #{loan.id}
                                    </p>
                                </div>
                                <div className="flex gap-2">
                                    <Link href={route('books.show', loan.book.id)}>
                                        <PrimaryButton>
                                            View Book
                                        </PrimaryButton>
                                    </Link>
                                </div>
                            </div>

                            {/* Book Information */}
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                <div className="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                    <h2 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                        📚 Book Information
                                    </h2>
                                    <div className="space-y-3">
                                        <div>
                                            <span className="block text-sm font-medium text-gray-500 dark:text-gray-400">
                                                Title
                                            </span>
                                            <Link
                                                href={route('books.show', loan.book.id)}
                                                className="text-lg text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300"
                                            >
                                                {loan.book.title}
                                            </Link>
                                        </div>
                                        <div>
                                            <span className="block text-sm font-medium text-gray-500 dark:text-gray-400">
                                                Author
                                            </span>
                                            <span className="text-gray-900 dark:text-white">
                                                {loan.book.author}
                                            </span>
                                        </div>
                                        <div>
                                            <span className="block text-sm font-medium text-gray-500 dark:text-gray-400">
                                                ISBN
                                            </span>
                                            <span className="text-gray-900 dark:text-white text-sm">
                                                {loan.book.isbn}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div className="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                    <h2 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                        👤 Borrower Information
                                    </h2>
                                    <div className="space-y-3">
                                        <div>
                                            <span className="block text-sm font-medium text-gray-500 dark:text-gray-400">
                                                Name
                                            </span>
                                            <span className="text-gray-900 dark:text-white">
                                                {loan.user.name}
                                            </span>
                                        </div>
                                        <div>
                                            <span className="block text-sm font-medium text-gray-500 dark:text-gray-400">
                                                Email
                                            </span>
                                            <span className="text-gray-900 dark:text-white text-sm">
                                                {loan.user.email}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Loan Details */}
                            <div className="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 mb-8">
                                <h2 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                    📅 Loan Timeline
                                </h2>
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <span className="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                            Borrowed Date
                                        </span>
                                        <span className="text-gray-900 dark:text-white">
                                            {new Date(loan.loan_date).toLocaleDateString()}
                                        </span>
                                    </div>
                                    <div>
                                        <span className="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                            Due Date
                                        </span>
                                        <span className={`font-semibold ${
                                            isOverdue
                                                ? 'text-red-600 dark:text-red-400'
                                                : 'text-gray-900 dark:text-white'
                                        }`}>
                                            {new Date(loan.due_date).toLocaleDateString()}
                                        </span>
                                    </div>
                                    <div>
                                        <span className="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                            Status
                                        </span>
                                        <span className={`inline-flex px-3 py-1 text-sm font-semibold rounded-full ${
                                            loan.returned_at
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                : isOverdue
                                                ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                                : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                                        }`}>
                                            {loan.returned_at
                                                ? 'Returned'
                                                : isOverdue
                                                ? `${daysOverdue} days overdue`
                                                : 'Active'
                                            }
                                        </span>
                                    </div>
                                </div>

                                {loan.returned_at && (
                                    <div className="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                                        <span className="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                            Returned Date
                                        </span>
                                        <span className="text-green-600 dark:text-green-400 font-semibold">
                                            {new Date(loan.returned_at).toLocaleDateString()}
                                        </span>
                                    </div>
                                )}
                            </div>

                            {/* Actions */}
                            {!loan.returned_at && (
                                <div className="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-lg p-6">
                                    <h2 className="text-lg font-semibold text-yellow-800 dark:text-yellow-200 mb-4">
                                        ⚠️ Active Loan
                                    </h2>
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <p className="text-yellow-800 dark:text-yellow-200">
                                                {isOverdue
                                                    ? `This loan is ${daysOverdue} days overdue. Please return the book as soon as possible.`
                                                    : 'This book is currently borrowed. Please return it by the due date.'
                                                }
                                            </p>
                                        </div>
                                        <div className="flex gap-2">
                                            <form method="POST" action={route('loans.extend', loan.id)}>
                                                <PrimaryButton
                                                    type="submit"
                                                    disabled={isOverdue}
                                                    className={isOverdue ? 'opacity-50 cursor-not-allowed' : ''}
                                                >
                                                    Extend 7 Days
                                                </PrimaryButton>
                                            </form>
                                            <form method="POST" action={route('loans.return', loan.id)}>
                                                <button
                                                    type="submit"
                                                    className="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-150"
                                                >
                                                    Return Book
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* Return Confirmation */}
                            {loan.returned_at && (
                                <div className="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-lg p-6">
                                    <h2 className="text-lg font-semibold text-green-800 dark:text-green-200 mb-4">
                                        ✅ Book Returned
                                    </h2>
                                    <p className="text-green-800 dark:text-green-200">
                                        This book has been successfully returned. Thank you for using our library!
                                    </p>
                                    <div className="mt-4">
                                        <Link href={route('books.show', loan.book.id)}>
                                            <PrimaryButton>
                                                Borrow This Book Again
                                            </PrimaryButton>
                                        </Link>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
