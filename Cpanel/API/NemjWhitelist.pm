package Cpanel::API::NemjWhitelist;

use Cpanel                  ();
use Cpanel::AdminBin::Call  ();
use Cpanel::LoadModule      ();
use Cpanel::Logger          ();
use Data::Dumper            ();

=head1 NAME

=head1 DESCRIPTION

=head1 FUNCTIONS

=head2 write_hosts

=cut

my $logger;

sub _initialize {
    $logger ||= Cpanel::Logger->new();
    return 1;
}

sub write_hosts {
    _initialize();
    my ( $args, $result ) = @_;

    my $path = $args->get('path');
    my $hosts = $args->get('hosts');
    my $val;

    $val = Cpanel::AdminBin::Call::call(
        'Nemanja',
        'Whitelist',
        'WRITE_HOST',
        $path,
	    $hosts,
    );

    return $val;
}

1;
