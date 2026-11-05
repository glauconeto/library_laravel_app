import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthCard from '@/Components/AuthCard';
import AuthCardLogo from '@/Components/AuthCardLogo';
import PrimaryButton from '@/Components/PrimaryButton';

interface VerifyEmailProps {
    status: string | null;
}

export default function VerifyEmail({ status }: VerifyEmailProps) {
    const { post, processing } = useForm({});

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('verification.send'));
    };

    return (
        <AuthCard>
            <Head title="Email Verification" />

            <div className="mb-4 text-sm text-gray-600 dark:text-gray-400">
                Obrigado por se cadastrar! Antes de começar, você poderia verificar seu endereço de e-mail clicando no link que acabamos de enviar? Se não recebeu o e-mail, podemos enviar outro.
            </div>

            {status === 'verification-link-sent' && (
                <div className="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                    Um novo link de verificação foi enviado para o endereço de e-mail que você forneceu durante o registro.
                </div>
            )}

            <form onSubmit={submit}>
                <div className="mt-4 flex items-center justify-between">
                    <PrimaryButton disabled={processing}>
                        Reenviar E-mail de Verificação
                    </PrimaryButton>

                    <Link
                        href={route('logout')}
                        method="post"
                        as="button"
                        className="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                    >
                        Sair
                    </Link>
                </div>
            </form>
        </AuthCard>
    );
}