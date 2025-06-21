<div class="form-group">
    <label class="col-md-3 control-label">Linked Executive(s)</label>
    <div class="col-md-9">
        <table id="MarketingEmployeeTable" class="table table-hover table-bordered table-striped">
            <tbody>
                <tr>
                    <th>Date</th>
                    <th>Employee</th>
                    <!-- <th>Average Sales</th> -->
                    <th>Actions</th>
                </tr>
                @if(isset($customerdata['user_customer_shares']) && !empty($customerdata['user_customer_shares']))
                    @foreach($customerdata['user_customer_shares'] as $userinfo)
                        <tr>
                            <td>
                                <input type="hidden" name="marketing_user_shares[]" value="100">
                                <input class="form-control" type="date" name="user_dates[]" value="{{$userinfo['user_date']}}" required>
                            </td>
                            <td>
                                <select class="form-control" name="marketing_user_ids[]" required>
                                    <option value="">Please Select</option>
                                    @foreach($users as $user)
                                        <option value="{{$user['id']}}" @if($user['id'] == $userinfo['user_id']) selected @endif>{{$user['name']}}</option>
                                    @endforeach
                                </select>
                            </td>
                            <!-- <td>
                                <input type="number" name="average_sales[]" class="form-control" placeholder="Enter Average Sales" value="{{$userinfo['average_sales']}}" required>
                            </td> -->
                            <td>
                                <a title="Remove" class="btn btn-sm red " onClick="$(this).closest('tr').remove();" href="javascript:;"> <i class="fa fa-times"></i></a>
                            </td>
                        </tr>
                    @endforeach
                @endif
                @if(isset($customerdata['linked_executive']) && !empty($customerdata['linked_executive']))
                    <tr>
                        <td>
                            <input class="form-control" type="date" name="user_dates[]" value="{{$customerdata['created_at']}}" required>
                        </td>
                        <td>
                            <select class="form-control" name="marketing_user_ids[]" required>
                                <option value="">Please Select</option>
                                @foreach($users as $user)
                                    <option value="{{$user['id']}}" @if($user['id'] == $customerdata['linked_executive']) selected @endif>{{$user['name']}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            
                        </td>
                    </tr> 
                @endif
            </tbody>
        </table>
        <input type="button" id="addMarketingEmployee" value="Add New" />
    </div>
</div>