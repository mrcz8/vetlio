<?php

namespace App\Filament\App\Actions;

use App\Enums\Icons\PhosphorIcons;
use App\Mail\GenericMail;
use Closure;
use CodeWithDennis\SimpleAlert\Components\SimpleAlert;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;

class SendEmailAction extends Action
{
    protected ?array $receivers = null;
    protected ?array $ccReceivers = null;
    protected ?array $bccReceivers = null;
    protected string|\Closure|null $subject = null;
    protected string|\Closure|null $body = null;
    protected bool $useQueue = false;

    protected string|null $attachment = null;
    protected string|\Closure|null $attachmentName = null;

    public static function getDefaultName(): ?string
    {
        return 'sendEmail';
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->icon(Heroicon::Envelope);
        $this->modalIcon(PhosphorIcons::Envelope);
        $this->successNotificationTitle('Email sent successfully');
        $this->failureNotificationTitle('Error sending email');
        $this->hiddenLabel();
        $this->tooltip('Send email');
        $this->outlined();

        $this->schema(fn() => [
            Hidden::make('showCc')
                ->live()
                ->default(fn() => filled($this->evaluate($this->ccReceivers))),

            Hidden::make('showBcc')
                ->reactive()
                ->default(fn() => filled($this->evaluate($this->bccReceivers))),

            TagsInput::make('receivers')
                ->hintActions([
                    Action::make('addCCReceiver')
                        ->icon(PhosphorIcons::Users)
                        ->label('Add CC')
                        ->action(function (Get $get, Set $set) {
                            $set('showCc', !(bool) $get('showCc'));
                        }),
                    Action::make('addBCCReceiver')
                        ->icon(PhosphorIcons::Users)
                        ->label('Add BCC')
                        ->action(function (Get $get, Set $set) {
                            $set('showBcc', !(bool) $get('showBcc'));
                        }),
                ])
                ->label('Recipients')
                ->placeholder('Enter email addresses and confirm with Enter…')
                ->required()
                ->separator(',')
                ->default($this->evaluate($this->receivers))
                ->helperText('At least one recipient is required.'),

            TagsInput::make('ccReceivers')
                ->label('CC')
                ->separator(',')
                ->visible(fn(Get $get) => (bool) $get('showCc'))
                ->default($this->evaluate($this->ccReceivers)),

            TagsInput::make('bccReceivers')
                ->label('BCC')
                ->separator(',')
                ->visible(fn(Get $get) => (bool) $get('showBcc'))
                ->default($this->evaluate($this->bccReceivers)),

            TextInput::make('subject')
                ->label('Subject')
                ->required()
                ->maxLength(255)
                ->default($this->evaluate($this->subject)),

            RichEditor::make('body')
                ->label('Message')
                ->extraInputAttributes(['style' => 'min-height: 200px'])
                ->required()
                ->default($this->evaluate($this->body)),

            SimpleAlert::make('existing_attachment')
                ->title('Attachment to send')
                ->description(fn() => $this->evaluate($this->attachmentName))
                ->columnSpanFull()
                ->info()
                ->icon(Heroicon::PaperClip)
                ->visible(fn() => $this->attachment !== null),

            FileUpload::make('extra_attachments')
                ->label('Additional attachments')
                ->multiple()
                ->disk('local') // everything stored on 'local'
                ->directory('emails/attachments')
                ->visibility('private')
                ->downloadable()
                ->maxSize(10240)
                ->hint('Add PDFs or images as extra attachments.'),
        ]);

        $this->action(function (array $data) {
            $to = $data['receivers'] ?? [];
            $cc = $data['ccReceivers'] ?? [];
            $bcc = $data['bccReceivers'] ?? [];

            if (empty($to) && empty($cc) && empty($bcc)) {
                $this->failureNotificationTitle('You must specify at least one recipient (To, CC or BCC).');
                $this->failure();
                return;
            }

            $primaryAttachmentPath = $this->attachment;

            $extraAttachments = array_values($data['extra_attachments'] ?? []);

            $mailable = new GenericMail(
                title: $data['subject'],
                body: $data['body'],
                attachmentPath: $primaryAttachmentPath,
                extraAttachments: $extraAttachments,
                disk: 'local',
            );

            $this->useQueue
                ? Mail::to($to)->cc($cc)->bcc($bcc)->queue($mailable)
                : Mail::to($to)->cc($cc)->bcc($bcc)->send($mailable);

            $this->success();
        });
    }

    public function receivers(array|callable|null $receivers): static
    {
        $this->receivers = $receivers;
        return $this;
    }

    public function ccReceivers(array|callable|null $cc): static
    {
        $this->ccReceivers = $cc;
        return $this;
    }

    public function bccReceivers(array|callable|null $bcc): static
    {
        $this->bccReceivers = $bcc;
        return $this;
    }

    public function subject(string|\Closure|null $subject): static
    {
        $this->subject = $subject;
        return $this;
    }

    public function body(string|\Closure|null $body): static
    {
        $this->body = $body;
        return $this;
    }

    public function queue(bool $state = true): static
    {
        $this->useQueue = $state;
        return $this;
    }

    public function attachment(string $relativePath, string|\Closure|null $displayName = null): static
    {
        $this->attachment = $relativePath; // e.g. 'emails/attachments/invoice-123.pdf'
        $this->attachmentName = $displayName; // e.g. 'invoice-123.pdf'
        return $this;
    }
}
