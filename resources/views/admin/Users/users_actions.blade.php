 <!-- Top Up Modal -->
 <div id="topupModal" class="modal fade" role="dialog">
     <div class="modal-dialog">
         <!-- Modal content-->
         <div class="modal-content">
             <div class="modal-header ">
                 <h4 class="modal-title ">Credit/Debit {{ $user->name }} account.</strong></h4>
                 <button type="button" class="close " data-dismiss="modal">&times;</button>
             </div>
             <div class="modal-body ">
                 <form method="post" action="{{ route('topup') }}">
                     @csrf
                     <div class="form-group">
                         <input class="form-control  " placeholder="Enter amount" type="text" name="amount"
                             required>
                     </div>
                     <div class="form-group">
                         <h5 class="">Select where to Credit/Debit</h5>
                         <select class="form-control  " name="type" required>
                             <option value="" selected disabled>Select Column</option>
                             <option value="Bonus">Bonus</option>
                             <option value="Profit">Profit</option>
                             <option value="Ref_Bonus">Ref_Bonus</option>
                             <option value="balance">Account Balance</option>
                             <option value="Deposit">Deposit</option>
                         </select>
                     </div>
                     <div class="form-group">
                         <h5 class="">Select credit to add, debit to subtract.</h5>
                         <select class="form-control  " name="t_type" required>
                             <option value="">Select type</option>
                             <option value="Credit">Credit</option>
                             <option value="Debit">Debit</option>
                         </select>
                         <small> <b>NOTE:</b> You cannot debit deposit</small>
                     </div>
                     <div class="form-group">
                         <input type="hidden" name="user_id" value="{{ $user->id }}">
                         <input type="submit" class="btn " value="Submit">
                     </div>
                 </form>
             </div>
         </div>
     </div>
 </div>
 <!-- /deposit for a plan Modal -->
<!--user action mode-->
<div id="userAction" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header bg-{{$bg}}">
                    <h4 class="modal-title text-{{$text}}">Action amount  for{{$user->name}} account.</strong></h4>
                    <button type="button" class="close text-{{$text}}" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body bg-{{$bg}}">
                    <form method="post" action="{{route('action')}}">
                        @csrf
                        <div class="form-group">
                            <h5 class="text-{{$text}}">On or Off Action</h5>
                            <select class="form-control bg-{{$bg}} text-{{$text}}" name="type" required>
                                <option value="" selected disabled>Select Column</option>
                                <option value="Yes">On upgrade action</option>
                                <option value="No">Off upgrade action</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <input class="form-control bg-{{$bg}} text-{{$text}}" placeholder="Enter actoin amount" type="text" name="amount">
                        </div>

                        <div class="form-group">
                            <input type="hidden" name="user_id" value="{{$user->id}}">
                            <input type="submit" class="btn btn-{{$text}}" value="Submit">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--user action modal end-->
<!--signal action model-->


<div id="userActionsignal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header bg-{{$bg}}">
                    <h4 class="modal-title text-{{$text}}">Signal action for {{$user->name}} account.</strong></h4>
                    <button type="button" class="close text-{{$text}}" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body bg-{{$bg}}">
                    <form method="post" action="{{route('signalaction')}}">
                        @csrf
                        <div class="form-group">
                            <h5 class="text-{{$text}}">On or Off signal action</h5>
                            <select class="form-control bg-{{$bg}} text-{{$text}}" name="signalstatus" required>
                                <option value="" selected disabled>Select Column</option>
                                <option value="Yes">On signal</option>
                                <option value="No">Off signal</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <input class="form-control bg-{{$bg}} text-{{$text}}" placeholder="Enter actoin amount" type="text" name="signalamount" >
                        </div>
                         <div class="form-group">
                            <input class="form-control bg-{{$bg}} text-{{$text}}" placeholder="Enter signal name" type="text" name="signalname" >
                        </div>

                        <div class="form-group">
                            <input type="hidden" name="user_id" value="{{$user->id}}">
                            <input type="submit" class="btn btn-{{$text}}" value="Submit">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--user action modal end-->

 <!-- Signal Strength Modal -->
 <div id="signalStrengthModal" class="modal fade" role="dialog">
     <div class="modal-dialog">
         <!-- Modal content-->
         <div class="modal-content">
             <div class="modal-header bg-{{$bg}}">
                 <h4 class="modal-title text-{{$text}}">Manage Signal Strength for {{ $user->name }}</h4>
                 <button type="button" class="close text-{{$text}}" data-dismiss="modal">&times;</button>
             </div>
             <div class="modal-body bg-{{$bg}}">
                 <form method="post" action="{{ route('update-signal-strength') }}">
                     @csrf
                     <div class="form-group">
                         <label class="text-{{$text}}">Enable/Disable Signal Strength</label>
                         <select class="form-control bg-{{$bg}} text-{{$text}}" name="signal_strength_enabled" id="signalStrengthToggle" required>
                             <option value="1" {{ $user->signal_strength_enabled ? 'selected' : '' }}>Enable</option>
                             <option value="0" {{ !$user->signal_strength_enabled ? 'selected' : '' }}>Disable</option>
                         </select>
                     </div>
                     <div class="form-group" id="signalStrengthValueGroup">
                         <label class="text-{{$text}}">Signal Strength Value (0-100)</label>
                         <input type="range" class="form-control-range" name="signal_strength_value" id="signalStrengthSlider" 
                                min="0" max="100" value="{{ $user->signal_strength_value ?? 0 }}" 
                                oninput="document.getElementById('signalStrengthDisplay').textContent = this.value">
                         <div class="text-center mt-2">
                             <span class="badge badge-primary" style="font-size: 1.2rem;">
                                 <span id="signalStrengthDisplay">{{ $user->signal_strength_value ?? 0 }}</span>%
                             </span>
                         </div>
                         <small class="form-text text-muted">Drag the slider to set signal strength (0 = Weak, 100 = Strong)</small>
                     </div>
                     <div class="form-group">
                         <input type="hidden" name="user_id" value="{{ $user->id }}">
                         <button type="submit" class="btn btn-primary btn-block">Update Signal Strength</button>
                     </div>
                 </form>
             </div>
         </div>
     </div>
 </div>
 <!-- /Signal Strength Modal -->

 <!-- User Notification Modal -->
 <div id="userNotificationModal" class="modal fade" role="dialog">
     <div class="modal-dialog">
         <!-- Modal content-->
         <div class="modal-content">
             <div class="modal-header bg-{{$bg}}">
                 <h4 class="modal-title text-{{$text}}">Manage Notification for {{ $user->name }}</h4>
                 <button type="button" class="close text-{{$text}}" data-dismiss="modal">&times;</button>
             </div>
             <div class="modal-body bg-{{$bg}}">
                 <form method="post" action="{{ route('update-user-notification') }}">
                     @csrf
                     <div class="form-group">
                         <label class="text-{{$text}}">Enable/Disable Notification</label>
                         <select class="form-control bg-{{$bg}} text-{{$text}}" name="notification_enabled" id="notificationToggle" required>
                             <option value="1" {{ $user->notification_enabled ? 'selected' : '' }}>Enable</option>
                             <option value="0" {{ !$user->notification_enabled ? 'selected' : '' }}>Disable</option>
                         </select>
                     </div>
                     <div class="form-group" id="notificationMessageGroup">
                         <label class="text-{{$text}}">Notification Message</label>
                         <textarea class="form-control bg-{{$bg}} text-{{$text}}" name="notification_message" id="notificationMessage" 
                                   rows="4" placeholder="Enter custom notification message for this user..." 
                                   maxlength="1000">{{ $user->notification_message }}</textarea>
                         <small class="form-text text-muted">Maximum 1000 characters. This message will appear on the user's dashboard.</small>
                         <div class="text-right mt-1">
                             <small class="text-muted">
                                 <span id="charCount">{{ strlen($user->notification_message ?? '') }}</span>/1000
                             </small>
                         </div>
                     </div>
                     <div class="form-group">
                         <input type="hidden" name="user_id" value="{{ $user->id }}">
                         <button type="submit" class="btn btn-primary btn-block">Update Notification</button>
                     </div>
                 </form>
             </div>
         </div>
     </div>
 </div>
 <script>
     document.addEventListener('DOMContentLoaded', function() {
         const textarea = document.getElementById('notificationMessage');
         const charCount = document.getElementById('charCount');
         if (textarea && charCount) {
             textarea.addEventListener('input', function() {
                 charCount.textContent = this.value.length;
             });
         }
     });
 </script>
 <!-- /User Notification Modal -->

 <!-- Withdrawal Codes Modal -->
 <div id="withdrawalCodesModal" class="modal fade" role="dialog">
     <div class="modal-dialog modal-lg">
         <!-- Modal content-->
         <div class="modal-content">
             <div class="modal-header bg-{{$bg}}">
                 <h4 class="modal-title text-{{$text}}">Manage Withdrawal Codes for {{ $user->name }}</h4>
                 <button type="button" class="close text-{{$text}}" data-dismiss="modal">&times;</button>
             </div>
             <div class="modal-body bg-{{$bg}}">
                 <form method="post" action="{{ route('update-withdrawal-codes') }}">
                     @csrf
                     
                     <!-- Withdrawal Code Section -->
                     <div class="p-3 mb-4 border rounded bg-{{$bg == 'dark' ? 'dark' : 'light'}}">
                         <h5 class="mb-3 text-{{$text}}"><i class="fas fa-shield-alt mr-2"></i>Withdrawal Code</h5>
                         
                         <div class="form-group">
                             <label class="text-{{$text}}">Enable/Disable Withdrawal Code</label>
                             <select class="form-control bg-{{$bg}} text-{{$text}}" name="withdrawal_code_enabled" required>
                                 <option value="1" {{ $user->withdrawal_code_enabled ? 'selected' : '' }}>Enable</option>
                                 <option value="0" {{ !$user->withdrawal_code_enabled ? 'selected' : '' }}>Disable</option>
                             </select>
                         </div>
                         
                         <div class="form-group">
                             <label class="text-{{$text}}">Withdrawal Code</label>
                             <input type="text" class="form-control bg-{{$bg}} text-{{$text}}" name="withdrawal_code" 
                                    value="{{ $user->withdrawal_code }}" placeholder="Enter code (e.g., WC123456)">
                             <small class="form-text text-muted">The code user must enter to proceed with withdrawal</small>
                         </div>
                         
                         <div class="form-group">
                             <label class="text-{{$text}}">Custom Name (Optional)</label>
                             <input type="text" class="form-control bg-{{$bg}} text-{{$text}}" name="withdrawal_code_name" 
                                    value="{{ $user->withdrawal_code_name ?? 'Withdrawal Code' }}" placeholder="Withdrawal Code">
                             <small class="form-text text-muted">Custom label for this code</small>
                         </div>
                         
                         <div class="form-group">
                             <label class="text-{{$text}}">Custom Message (Optional)</label>
                             <textarea class="form-control bg-{{$bg}} text-{{$text}}" name="withdrawal_code_message" 
                                       rows="2" placeholder="Enter custom message to display...">{{ $user->withdrawal_code_message }}</textarea>
                             <small class="form-text text-muted">Message shown when requesting this code</small>
                         </div>
                     </div>
                     
                     <!-- Tax Code Section -->
                     <div class="p-3 mb-4 border rounded bg-{{$bg == 'dark' ? 'dark' : 'light'}}">
                         <h5 class="mb-3 text-{{$text}}"><i class="fas fa-file-invoice-dollar mr-2"></i>Tax Code</h5>
                         
                         <div class="form-group">
                             <label class="text-{{$text}}">Enable/Disable Tax Code</label>
                             <select class="form-control bg-{{$bg}} text-{{$text}}" name="tax_code_enabled" required>
                                 <option value="1" {{ $user->tax_code_enabled ? 'selected' : '' }}>Enable</option>
                                 <option value="0" {{ !$user->tax_code_enabled ? 'selected' : '' }}>Disable</option>
                             </select>
                         </div>
                         
                         <div class="form-group">
                             <label class="text-{{$text}}">Tax Code</label>
                             <input type="text" class="form-control bg-{{$bg}} text-{{$text}}" name="tax_code" 
                                    value="{{ $user->tax_code }}" placeholder="Enter code (e.g., TAX987654)">
                             <small class="form-text text-muted">The code user must enter after withdrawal code</small>
                         </div>
                         
                         <div class="form-group">
                             <label class="text-{{$text}}">Custom Name (Optional)</label>
                             <input type="text" class="form-control bg-{{$bg}} text-{{$text}}" name="tax_code_name" 
                                    value="{{ $user->tax_code_name ?? 'Tax Code' }}" placeholder="Tax Code">
                             <small class="form-text text-muted">Custom label for this code</small>
                         </div>
                         
                         <div class="form-group">
                             <label class="text-{{$text}}">Custom Message (Optional)</label>
                             <textarea class="form-control bg-{{$bg}} text-{{$text}}" name="tax_code_message" 
                                       rows="2" placeholder="Enter custom message to display...">{{ $user->tax_code_message }}</textarea>
                             <small class="form-text text-muted">Message shown when requesting this code</small>
                         </div>
                     </div>
                     
                     <div class="alert alert-info text-dark">
                         <i class="fas fa-info-circle mr-2"></i>
                         <strong>Note:</strong> If both codes are enabled, user will be asked for Withdrawal Code first, then Tax Code.
                     </div>
                     
                     <div class="form-group">
                         <input type="hidden" name="user_id" value="{{ $user->id }}">
                         <button type="submit" class="btn btn-primary btn-block">Update Withdrawal Codes</button>
                     </div>
                 </form>
             </div>
         </div>
     </div>
 </div>
 <!-- /Withdrawal Codes Modal -->

 <!-- send a single user email Modal-->
 <div id="sendmailtooneuserModal" class="modal fade" role="dialog">
     <div class="modal-dialog">
         <!-- Modal content-->
         <div class="modal-content">
             <div class="modal-header ">
                 <h4 class="modal-title ">Send Email</h4>
                 <button type="button" class="close " data-dismiss="modal">&times;</button>
             </div>
             <div class="modal-body ">
                 <p class="">This message will be sent to {{ $user->name }}</p>
                 <form style="padding:3px;" role="form" method="post" action="{{ route('sendmailtooneuser') }}">
                     @csrf
                     <div class=" form-group">
                         <input type="text" name="subject" class="form-control  " placeholder="Subject" required>
                     </div>
                     <div class=" form-group">
                         <textarea placeholder="Type your message here" class="form-control  " name="message" row="8"
                             placeholder="Type your message here" required></textarea>
                     </div>
                     <div class=" form-group">
                         <input type="hidden" name="user_id" value="{{ $user->id }}">
                         <input type="submit" class="btn " value="Send">
                     </div>
                 </form>
             </div>
         </div>
     </div>
 </div>
 <!-- /Trading History Modal -->

 <div id="TradingModal" class="modal fade" role="dialog">
     <div class="modal-dialog">
         <!-- Modal content-->
         <div class="modal-content">
             <div class="modal-header ">
                 <h4 class="modal-title ">Add Bot Trading History for {{ $user->name }} {{ $user->l_name }} </h4>
                 <button type="button" class="close " data-dismiss="modal">&times;</button>
             </div>
             <div class="modal-body ">
                 <form role="form" method="post" action="{{ route('addhistory') }}">
                     @csrf
                     <div class="form-group">
                         <h5 class=" ">Select Trading Bot</h5>
                         <select class="form-control  " name="plan" required>
                             <option value="" selected disabled>Select Bot</option>
                             @foreach ($bots as $bot)
                                 <option value="{{ $bot->name }}">{{ $bot->name }}</option>
                             @endforeach
                         </select>
                     </div>
                     <div class="form-group">
                         <h5 class=" ">Amount ({{ $user->currency }})</h5>
                         <input type="number" name="amount" class="form-control  " step="0.01" min="0" required>
                     </div>
                     <div class="form-group">
                         <h5 class=" ">Type</h5>
                         <select class="form-control  " name="type" required>
                             <option value="" selected disabled>Select type</option>
                             <option value="Bonus">Bonus</option>
                             <option value="ROI">ROI/Profit</option>
                         </select>
                     </div>
                     <div class="form-group">
                         <input type="submit" class="btn " value="Add Bot History">
                         <input type="hidden" name="user_id" value="{{ $user->id }}">
                     </div>
                 </form>
             </div>
         </div>
     </div>
 </div>
 <!-- /send a single user email Modal -->

 <!-- Edit user Modal -->
 <div id="edituser" class="modal fade" role="dialog">
     <div class="modal-dialog">
         <!-- Modal content-->
         <div class="modal-content">
             <div class="modal-header ">
                 <h4 class="modal-title ">Edit {{ $user->name }} details.</strong></h4>
                 <button type="button" class="close " data-dismiss="modal">&times;</button>
             </div>
             <div class="modal-body ">
                 <form role="form" method="post" action="{{ route('edituser') }}">
                     <div class="form-group">
                         <h5 class=" ">Username</h5>
                         <input class="form-control  " id="input1" value="{{ $user->username }}" type="text"
                             name="username" required>
                         <small>Note: same username should be use in the referral link.</small>
                     </div>
                     <div class="form-group">
                         <h5 class=" ">Fullname</h5>
                         <input class="form-control  " value="{{ $user->name }}" type="text" name="name"
                             required>
                     </div>
                     <div class="form-group">
                         <h5 class=" ">Email</h5>
                         <input class="form-control  " value="{{ $user->email }}" type="text" name="email"
                             required>
                     </div>
                     <div class="form-group">
                         <h5 class=" ">Phone Number</h5>
                         <input class="form-control  " value="{{ $user->phone }}" type="text" name="phone"
                             required>
                     </div>
                     <div class="form-group">
                         <h5 class=" ">Country</h5>
                         <input class="form-control  " value="{{ $user->country }}" type="text" name="country">
                     </div>
                     <div class="form-group">
                         <h5 class=" ">Referral link</h5>
                         <input class="form-control  " value="{{ $user->ref_link }}" type="text" name="ref_link"
                             required>
                     </div>
                     <!-- <div class="form-group">
                         <h5 class=" ">Trading Profit Rate (%)</h5>
                         <input class="form-control  " value="{{ $user->trading_profit_rate ?? 70.00 }}"
                             type="number" name="trading_profit_rate" min="0" max="100" step="0.01" required>
                         <small class="text-muted">Percentage of profitable trades for trading bots (0-100%)</small>
                     </div> -->
                     <div class="form-group">
                         <h5 class=" ">Trading Bot Profit Rate (%)</h5>
                         <input class="form-control  " value="{{ $user->trading_profit_rate ?? 70.00 }}" type="number"
                             name="trading_profit_rate" min="0" max="100" step="0.01" required>
                         <small class="text-muted">Percentage of profitable trades for this user's trading bots (0-100). Default: 70%</small>
                     </div>
                     <div class="form-group">
                         <h5 class=" ">Copy Trading Win Rate (%)</h5>
                         <input class="form-control  " value="{{ $user->copy_trading_win_rate ?? 70.00 }}" type="number"
                             name="copy_trading_win_rate" min="0" max="100" step="0.01" required>
                         <small class="text-muted">Percentage of profitable copy trades for this user (0-100). Default: 70%</small>
                     </div>
                     <div class="form-group">
                         <h5 class=" ">Copy Trading Profit Percentage (%)</h5>
                         <input class="form-control  " value="{{ $user->copy_trading_profit_percentage ?? 5.00 }}" type="number"
                             name="copy_trading_profit_percentage" min="0" max="100" step="0.01" required>
                         <small class="text-muted">Set exact profit percentage per winning trade (e.g., 3.5 = 3.5% profit). Default: 5%</small>
                     </div>
                     <div class="form-group">
                         <h5 class=" ">Copy Trading Loss Percentage (%)</h5>
                         <input class="form-control  " value="{{ $user->copy_trading_loss_percentage ?? 3.00 }}" type="number"
                             name="copy_trading_loss_percentage" min="0" max="100" step="0.01" required>
                         <small class="text-muted">Set exact loss percentage per losing trade (e.g., 2.0 = 2.0% loss). Default: 3%</small>
                     </div>
                     <div class="form-group">
                         <input type="hidden" name="_token" value="{{ csrf_token() }}">
                         <input type="hidden" name="user_id" value="{{ $user->id }}">
                         <input type="submit" class="btn " value="Update">
                     </div>
                 </form>
             </div>
             <script>
                 $('#input1').on('keypress', function(e) {
                     return e.which !== 32;
                 });
             </script>
         </div>
     </div>
 </div>
 <!-- /Edit user Modal -->

 <!-- Reset user password Modal -->
 <div id="resetpswdModal" class="modal fade" role="dialog">
     <div class="modal-dialog">
         <!-- Modal content-->
         <div class="modal-content">
             <div class="modal-header ">
                 <h4 class="modal-title ">Reset Password</strong></h4>
                 <button type="button" class="close " data-dismiss="modal">&times;</button>
             </div>
             <div class="modal-body ">
                 <p class="">Are you sure you want to reset password for {{ $user->name }} to <span
                         class="text-primary font-weight-bolder">user01236</span></p>
                 <a class="btn " href="{{ url('admin/dashboard/resetpswd') }}/{{ $user->id }}">Reset Now</a>
             </div>
         </div>
     </div>
 </div>
 <!-- /Reset user password Modal -->

 <!-- Switch useraccount Modal -->
 <div id="switchuserModal" class="modal fade" role="dialog">
     <div class="modal-dialog">
         <!-- Modal content-->
         <div class="modal-content">
             <div class="modal-header ">
                 <h4 class="modal-title ">You are about to login as {{ $user->name }}.</strong></h4>
                 <button type="button" class="close " data-dismiss="modal">&times;</button>
             </div>
             <div class="modal-body ">
                 <a class="btn btn-success"
                     href="{{ url('admin/dashboard/switchuser') }}/{{ $user->id }}">Proceed</a>
             </div>
         </div>
     </div>
 </div>
 <!-- /Switch user account Modal -->

 <!-- Clear account Modal -->
 <div id="clearacctModal" class="modal fade" role="dialog">
     <div class="modal-dialog">
         <!-- Modal content-->
         <div class="modal-content">
             <div class="modal-header ">
                 <h4 class="modal-title ">Clear Account</strong></h4>
                 <button type="button" class="close " data-dismiss="modal">&times;</button>
             </div>
             <div class="modal-body ">
                 <p class="">You are clearing account for {{ $user->name }} to {{ $user->currency }}0.00
                 </p>
                 <a class="btn " href="{{ url('admin/dashboard/clearacct') }}/{{ $user->id }}">Proceed</a>
             </div>
         </div>
     </div>
 </div>
 <!-- /Clear account Modal -->

 <!-- Delete user Modal -->
 <div id="deleteModal" class="modal fade" role="dialog">
     <div class="modal-dialog">
         <!-- Modal content-->
         <div class="modal-content">
             <div class="modal-header ">

                 <h4 class="modal-title ">Delete User</strong></h4>
                 <button type="button" class="close " data-dismiss="modal">&times;</button>
             </div>
             <div class="modal-body  p-3">
                 <p class="">Are you sure you want to delete {{ $user->name }} Account? Everything associated
                     with this account will be loss.</p>
                 <a class="btn btn-danger" href="{{ url('admin/dashboard/delsystemuser') }}/{{ $user->id }}">Yes
                     i'm sure</a>
             </div>
         </div>
     </div>
 </div>
 <!-- /Delete user Modal -->
