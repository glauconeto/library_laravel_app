import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../layouts/AppLayout';
import PrimaryButton from '../../Components/PrimaryButton';

export default function LoansShow({ loan, isOverdue, daysOverdue }) {
    return (
        <AppLayout>
            <Head title={`Detalhes do Empréstimo - ${loan.book.title}`} />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                        <div className="p-6">
                            {/* Header */}
                            <div className="flex justify-between items-start mb-6">
                                <div>
                                    <h1 className="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                        Detalhes do Empréstimo
                                    </h1>
                                    <p className="text-gray-600 dark:text-gray-400">
                                        Empréstimo ID: #{loan.id}
                                    </p>
                                </div>
                                <div className="flex gap-2">
                                    <Link href={route('books.show', loan.book.id)}>
                                        <PrimaryButton>
                                            Ver Livro
                                        </PrimaryButton>
                                    </Link>
                                </div>
                            </div>

                            {/* Book Information */}
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                <div className="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                    <h2 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                        📚 Informações do Livro
                                    </h2>
                                    <div className="space-y-3">
                                        <div>
                                            <span className="block text-sm font-medium text-gray-500 dark:text-gray-400">
                                                Título
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
                                                Autor
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
                                        👤 Informações do Usuário
                                    </h2>
                                    <div className="space-y-3">
                                        <div>
                                            <span className="block text-sm font-medium text-gray-500 dark:text-gray-400">
                                                Nome
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
                                    📅 Linha do Tempo do Empréstimo
                                </h2>
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <span className="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                            Data de Retirada
                                        </span>
                                        <span className="text-gray-900 dark:text-white">
                                            {new Date(loan.loan_date).toLocaleDateString()}
                                        </span>
                                    </div>
                                    <div>
                                        <span className="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                            Data de Devolução
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
                                                ? 'Devolvido'
                                                : isOverdue
                                                ? `${daysOverdue} dias de atraso`
                                                : 'Ativo'
                                            }
                                        </span>
                                    </div>
                                </div>

                                {loan.returned_at && (
                                    <div className="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                                        <span className="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                            Data da Devolução
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
                                        ⚠️ Empréstimo Ativo
                                    </h2>
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <p className="text-yellow-800 dark:text-yellow-200">
                                                {isOverdue
                                                    ? `Este empréstimo está ${daysOverdue} dias atrasado. Por favor, devolva o livro o quanto antes.`
                                                    : 'Este livro está emprestado atualmente. Por favor, devolva até a data de vencimento.'
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
                                                    Estender 7 Dias
                                                </PrimaryButton>
                                            </form>
                                            <form method="POST" action={route('loans.return', loan.id)}>
                                                <button
                                                    type="submit"
                                                    className="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-150"
                                                >
                                                    Devolver Livro
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
                                        ✅ Livro Devolvido
                                    </h2>
                                    <p className="text-green-800 dark:text-green-200">
                                        Este livro foi devolvido com sucesso. Obrigado por usar nossa biblioteca!
                                    </p>
                                    <div className="mt-4">
                                        <Link href={route('books.show', loan.book.id)}>
                                            <PrimaryButton>
                                                Pegar Este Livro Emprestado Novamente
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
